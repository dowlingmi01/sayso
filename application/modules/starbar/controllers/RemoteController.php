<?php
/**
 * This controller handles the authentication and delivery of the client-specific Starbar
 * 
 * The principle point of entry is the index action which handles pre and post
 * install situations and routes the call to the correct Starbar action
 * 
 * Scenarios:
 * - user logs in / installs app / restarts / returns to client site
 * - user logs in / installs app / restarts / returns to any other site
 * - user logs in / logs out / another user logs in
 * - user logs in / deletes cookies / user logs in
 * - user logs in / deletes cookies / another user logs in
 * @author davidbjames
 *
 */
require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Starbar_RemoteController extends Api_GlobalController
{
    public function init() {
        if (!$this->_init()) {
            // setup error module to use the API error module
            Zend_Controller_Front::getInstance()
                ->getPlugin('Zend_Controller_Plugin_ErrorHandler')
                ->setErrorHandlerModule(Api_Bootstrap::$moduleName);
            // make sure errors output via JSONP renderer
            Api_Registry::set('renderer', new Api_Plugin_JsonPRenderer());
        }
    }
    
    /**
     * Main Starbar action used for:
     * - determining which starbar is requested
     * - routing to the correct starbar 
     * - determing starbar based on origin
     */
    public function indexAction () {
        $this->_acceptIdParameter('starbar_id');
        if ($this->starbar_id || $this->short_name) { // starbar identity provided
            
            $starbar = new Starbar();
            $this->view->starbar = $starbar;
            
            $this->_validateRequiredParameters(array('user_id', 'user_key', 'auth_key'));
            Api_Auth::getInstance()->authorizeApp($this->auth_key);
            
            if ($this->starbar_id) {
                $starbar->loadData($this->starbar_id);
            } else {
                $starbar->loadDataByUniqueFields(array('short_name' => $this->short_name));
                $this->starbar_id = $starbar->getId();
            }
            
            $starbarUserMap = new Starbar_UserMap();
            $starbarUserMap->loadDataByUniqueFields(array('user_id' => $this->user_id, 'starbar_id' => $starbar->getId()));
            
            $starbar->setUserMap($starbarUserMap);
            
            if ($this->visibility) {
                $starbar->setVisibility($this->visibility);
            }
            
            if ($this->client_user_logged_in) {
                
                if ($starbar->short_name !== $this->client_name) {
                    // customer site change!
                    // @todo handle this scenario
                }
                if ($starbar->short_name === $this->client_name) { 
                    // we are on the customer's web site (must be if these params are present)
                    // client vars: client_name, client_uuid, client_uuid_type
                    
                    $externalUserData = Db_Pdo::fetch('SELECT * FROM external_user WHERE user_id = ?', $this->user_id);
                    // so verify that the user id matches the uuid
                    // if NOT, then switch users
                    if ($externalUserData['uuid'] !== $this->client_uuid) {
                        // user change! (on same browser/computer)
                        // create/update external user
                        $externalUser = new External_User();
                        $externalUser->uuid = $this->client_uuid; // unique
                        $externalUser->uuid_type = $this->client_uuid_type;
                        $externalUser->starbar_id = $starbar->getId(); // unique
                        // note: we also treat this as a new "install":
                        $externalUser->install_ip_address = $_SERVER['REMOTE_ADDR'];
                        $externalUser->install_user_agent = $_SERVER['HTTP_USER_AGENT'];
                        $externalUser->install_begin_time = new Zend_Db_Expr('now()');
                        $externalUser->save(); // <-- inserts/updates based on uniques
                        
//                        $session = Api_UserSession::getInstance(User::getHash($this->user_id));
                        // reset all internal session namespaces and variables
                        // plus regenerate the id, however re-use the session file itself
//                        $this->user_key = $session->reset()->getKey();
                        
                        return $this->_forward(
                            'post-install-deliver', 
                            null, 
                            null, 
                            array('external_user' => $externalUser)
                        );
                    }
                } 
            }
            
            // get session and verify
            $session = Api_UserSession::getInstance($this->user_key);
            quickLog('session id ' . $this->user_key . ' with internal user id ' . $session->getId() . ' - user id parameter ' . $this->user_id);
            if ($session->hasId()) {
                if ($session->getId() !== (int) $this->user_id) {
                    // session user id is not the same as user_id in the request!
                    throw new Api_Exception(Api_Error::create(Api_Error::TARGET_USER_MISMATCH));
                } else {
                    // everything is ok, carry on.
                }
            } else {
                if ($this->user_key === User::getHash($this->user_id)) {
                    // user key validates with user id (via md5 hash), so just reset it on the session and carry on..
                    quickLog('user id (' . $this->user_id . ') missing from session but validates against user key, so just reset...');
                    $session->setId($this->user_id);
                } else {
                    // does not validate
                    throw new Api_Exception(Api_Error::create(Api_Error::SESSION_USER_MISSING, 'User id is missing from session and incoming user id (' . $this->user_id . ') does not validate against incoming user key (' . $this->user_key . ')'));
                }
            }
            
            $user = new User();
            $user->loadData($this->user_id);
            $user->setKey($this->user_key); // <-- keep session key in the loop
            $starbar->setUser($user);
            
            $gamer = Gamer::create($user->getId(), $starbar->getId());
			$session->setGamingUser($gamer);

	        $game = Game_Starbar::getInstance();
	        $game->checkin();    
            $this->_request->setParam(Api_AbstractController::GAME, $game);

            return $this->_forward(
                $starbar->short_name, 
                null, 
                null, 
                array('starbar' => $starbar)
            );
        } else { // no starbar id, so assume we are in the post install process
            return $this->_forward('post-install-deliver');
        }
        
    }
    
    /**
     * Pre-install
     * 
     * This action is executed via iframe from the install landing page.
     * - create external user (temporary user to facilitate install process)
     * - return cookies that tie the user's browser to sayso with:
     * 		- API auth key
     * 		- external user id
     * 		- install token (this is a hash used to prevent hacking the user id)
     * - idempotency: excellent. repeating this call will not create new external users.
     *   it will only update the install_token in the table and in the cookie
     */
    public function preInstallAction () {
        
        // if no external user identifier provided, then ignore this request
        if (!$this->client_uuid) return; 
        
        // validate
        $this->_validateRequiredParameters(array('auth_key', 'client_name', 'client_uuid', 'client_uuid_type', 'install_token', 'install_origination'));
        
        // authorize the app. This just checks that the key exists
        // and if not, throws an API exception
        Api_Auth::getInstance()->authorizeApp($this->auth_key);
        
        // the current user has hit this at least once before
        if (isset($_COOKIE['starbar_setup_install_token'])) {
            
            // lookup the user from install token
            // NOTE: this token is treated as unique based on the extreme
            // unlikelyhood of client-side getRandomToken() ever producing a dupe
            $externalUserTest = Db_Pdo::fetch(
            	'SELECT * FROM external_user WHERE install_token = ?', 
                $_COOKIE['starbar_setup_install_token']
            );
            
            // if the uuid of the found user is the same as the current user
            // then just re-use the install token from the cookie
            if ($externalUserTest['uuid'] === $this->client_uuid) { 
                $this->install_token = $_COOKIE['starbar_setup_install_token'];
            } 
            // else
            // this is a new user altogether so use the new install token
            // and create a new user (this is automatic below based on unique fields)
        }
        
        // determine Starbar from short name
        $starbarData = Db_Pdo::fetch('SELECT * FROM starbar WHERE short_name = ?', $this->client_name);
        
        $externalUser = new External_User();
        
        $externalUser->uuid = $this->client_uuid; // unique
        $externalUser->uuid_type = $this->client_uuid_type;
        $externalUser->starbar_id = $starbarData['id']; // unique
        $externalUser->install_token = $this->install_token;
        $externalUser->install_origination = $this->install_origination;
        $externalUser->save();
       
        // privacy header needed by IE to allow these 3rd party cookies to work 
        header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTR STP IND DEM"');

        // set cookies (to be retreived in post-install)
        setcookie('starbar_setup_auth_key', $this->auth_key, mktime(0,0,0,12,31,2030), '/');
        setcookie('starbar_setup_external_user_id', $externalUser->getId(), mktime(0,0,0,12,31,2030), '/');
        setcookie('starbar_setup_install_token', $this->install_token, mktime(0,0,0,12,31,2030), '/');
        
        return $this->_resultType(true);
    }
    
    /**
     * Post-install "setup"
     * 
     * This action is executed via iframe from the browser app
     * - passes the cookies (from above) to identify the external user
     * - associates that user with an IP address and a user agent string
     * - idempotency: excellent. even with potential "race condition" below
     *   (if (!$externalUser->install_ip_address)), it wouldn't hurt to 
     *   make multiple saves() on that record
     */
    public function postInstallSetupAction () {
        
        // IP / user agent
        
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        if (!$ipAddress) {
            throw new Api_Exception(Api_Error::create(Api_Error::APPLICATION_ERROR, 'IP Address could not be determined.'));
        }
        
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if (!$userAgent) {
            throw new Api_Exception(Api_Error::create(Api_Error::APPLICATION_ERROR, 'User agent could not be determined.'));
        }
        
        $externalUser = new External_User();
       
        // Two routes here:
        // 1. the user has our special cookies present to authenticate with
        // 2. no cookies, but the user is on the client site so automatically authenticate
        //    - this also supports situation where the user deletes all cookies
        // 3. error. worst that will happen is that starbar will not be displayed
        //    and user will have to return to client site and re-install or re-login
        
        if (isset($_COOKIE['starbar_setup_auth_key'])) { 
            // on other sites with cookies present (this is the most common case)
            
            // authenticate app
            
            Api_Auth::getInstance()->authorizeApp(@$_COOKIE['starbar_setup_auth_key']);
            
            if (empty($_COOKIE['starbar_setup_external_user_id'])) {
                throw new Api_Exception(Api_Error::create(Api_Error::APPLICATION_ERROR, 'Missing cookie starbar_setup_external_user_id'));
            }
            
            if (empty($_COOKIE['starbar_setup_install_token'])) {
                throw new Api_Exception(Api_Error::create(Api_Error::APPLICATION_ERROR, 'Missing cookie starbar_setup_install_token'));
            }
            
            // External User lookup
            
            $externalUser->loadData((int) $_COOKIE['starbar_setup_external_user_id']);
            
            // make sure user and install token match (in case user is trying to hack)
            if ($externalUser->install_token !== $_COOKIE['starbar_setup_install_token']) {
                throw new Api_Exception(Api_Error::create(Api_Error::APPLICATION_ERROR, 'Install token does not match for this client'));
            }
            
            // save IP / user agent to external user
        
            $externalUser->install_ip_address = $ipAddress;
            $externalUser->install_user_agent = $userAgent;
            $externalUser->save();
            
            Db_Pdo::execute('UPDATE external_user SET install_begin_time = now() WHERE id = ? AND install_begin_time IS NULL', $externalUser->getId());
        
            
        } else if ($this->client_uuid && strlen($this->client_uuid)) { // on customer site 
            
            $starbar = new Starbar();
            $starbar->loadDataByUniqueFields(array('short_name' => $this->client_name));
            
            $externalUser->uuid = $this->client_uuid;
            $externalUser->uuid_type = $this->client_uuid_type;
            $externalUser->starbar_id = $starbar->getId();
            $externalUser->install_ip_address = $ipAddress;
            $externalUser->install_user_agent = $userAgent;
            $externalUser->save();
            
            Db_Pdo::execute('UPDATE external_user SET install_begin_time = now() WHERE id = ? AND install_begin_time IS NULL', $externalUser->getId());
            
        } else {
            // no cookies / no client vars
            // starbar will not display. user must return to client site and login
            // NOTE: any other exceptions thrown in this method will be ignored (atm)
        }
    }
    
    /**
     * Post-install "deliver"
     * 
     * This action is called from /index above if no starbar ID is present
     * - this action happens nearly instantly after the above one, and therefore
     *   does not hold risk of stale references to IP or user agent
     * - external user is looked up via IP and user agent
     * - User is created
     * - grabs external uuid if it's useful (e.g. email, username)
     * - starts User session
     * - creates Starbar
     * - forwards to correct Starbar action
     * - idempotency: 
     */
    public function postInstallDeliverAction () {
        
        if ($this->external_user) {
            $externalUser = $this->external_user;
            goto externalUserExists;
        }
        
        // grab IP and user agent (this is how we identify)
        
        // NOTE: this is happening nearly instantly after the previous step, so we
        // can be reasonably certain of the user's identity
        
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        if (!$ipAddress) {
            throw new Api_Exception(Api_Error::create(Api_Error::APPLICATION_ERROR, 'IP Address could not be determined.'));
        }
        
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if (!$userAgent) {
            throw new Api_Exception(Api_Error::create(Api_Error::APPLICATION_ERROR, 'User agent could not be determined.'));
        }
        
        // find the user in the external user table
        
        $results = Db_Pdo::fetchAll('SELECT * FROM external_user WHERE install_ip_address = ? AND install_user_agent = ?', $ipAddress, $userAgent);
        
        if (!$results) exit(); // invalid request, ignore. IP/UA either were never inserted correctly or (more likely) have already been cleaned up
        
        if (count($results) > 1) {
            // keep throwing this and sending email since it is fairly rare and may indicate other problems
            throw new Api_Exception(Api_Error::create(Api_Error::APPLICATION_ERROR, 'More than one external user with the same IP address and user agent! IP: ' . $ipAddress . ', user agent: ' . $userAgent . ' for ' . count($results) . ' users'));
        }
            
        // External user
        
        $externalUser = new External_User();
        $externalUser->setData($results[0]);
    
        externalUserExists:
        
        // @todo handle race condition between the above select and the insert just below
        // problem is that other loads (e.g. browser tabs) may create extra users
        // if they hit this section simultaneously
        
        // User
        
        $user = new User();
        $newUser = false;
        
        // if user id already exists on external user, then use that
        
        if ($externalUser->user_id) {
            $user->setId($externalUser->user_id);
        } else { // .. otherwise create a new one
            $newUser = true;
            $stmt = Db_Pdo::execute('INSERT INTO user (id, created) VALUES (null, now())');
            $user->setId(Db_Pdo::getPdo()->lastInsertId());
            $externalUser->user_id = $user->getId();
            $externalUser->save();
        }
        
        // refresh the db data
        
        $user->reload();
        
        // grab any "free" user data coming from the login source
        
        switch ($externalUser->uuid_type) {
            case 'email' :
                $email = new User_Email();
                $email->email = $externalUser->uuid;
                $user->setEmail($email);
                $user->save();
                break;
            case 'username' :
                $user->username = $externalUser->uuid;
                $user->save();
                break;
            case 'integer' :
            case 'hash' :
            default :
                // do nothing for now
        }
        
        // start session
        
        if (Zend_Session::isStarted()) { // may already be started via BootstrapPlugin
            
            $userSession = Api_UserSession::getInstance();
            
            if ($userSession->hasId()) {
                if ($userSession->getId() !== $user->getId()) { 
                    // session has a different user id, so use that one instead
                    quickLog('User session (via key: ' . $userSession->getkey() . ') is for user id: ' . $userSession->getId() . ', however the user we just ' . ($newUser ? 'created' : 'retreived (via external user ' . $externalUser->getId() . ')') . ' is id: ' . $user->getId() . '. Deleting user record ' . $user->getId() . ' and using session user ' . $userSession->getId() . ' instead');
                    
                    // delete the user we just created
                    $user->delete();
                    // instantiate based on session user id
                    $user = new User();
                    $user->loadData($userSession->getId());
                } 
                // else session user id matches the one created.. good
            } else {
                throw new Api_Exception(Api_Error::create(Api_Error::SESSION_USER_MISSING));
            }
            // @todo check if session is started at the beginning of this function and just
            // use that user instaed of UI/IP method. only caveat is that the external user
            // holds the reference to the starbar. <-- should this id be in the session??
            // possibly move this condition to just before new User() above (after externalUserExists)
        } else {
            // brand new session. start it with the special hash key
            $userSession = Api_UserSession::getInstance(User::getHash($user->getId())); 
        }
        
        // set the user id on the session
        $userSession->setId($user->getId());
        // set the key on the user object so it is available for client-apps
        $user->setKey($userSession->getKey());
        
        quickLog('new Starbar - session id ' . $userSession->getKey() . ' - user id ' . $user->getId());
        
        // Starbar
    
        if (Registry::isRegistered('starbar')) {
            $starbar = Registry::getStarbar();
        } else {
            $starbar = new Starbar();
            $starbar->loadData($externalUser->starbar_id);  
        }
        
        $starbar->setUser($user); // <-- agreggate user to starbar
        
        // Map Starbar <-> User
        
        $starbarUserMap = new Starbar_UserMap();
        $starbarUserMap->user_id = $user->getId();
        $starbarUserMap->starbar_id = $starbar->getId();
        $starbarUserMap->active = 1;
        $starbarUserMap->save();
        
        $starbar->setUserMap($starbarUserMap->reload());

        // Add the four "important" ids to the request 
		$this->starbar_id = $starbar->getId();
		$this->user_id = $user->getId();
		$this->user_key = $userSession->getKey();
		$this->auth_key = $starbar->auth_key;
		
        // Game
        
        $gamer = Gamer::create($user->getId(), $starbar->getId());
        quickLog('Gaming id ' . $gamer->getGamingId());
        
        // save gaming user to session for easy retreival
        $userSession->setGamingUser($gamer);
        
        // trigger game transaction: *install* 
        $game = Game_Starbar::create($gamer, $this->_request, $starbar);
        $game->install();
        $this->_request->setParam(Api_AbstractController::GAME, $game);
        
        // now we know which starbar, route to the appropriate starbar action:
        return $this->_forward(
            $starbar->short_name, 
            null, 
            null, 
            array('starbar' => $starbar)
        );
    }
    
    /**
     * Lady Gaga Starbar
     * @todo add to starbar table if we decide to use it
     */
    public function gagaAction () 
    {
        $this->render();
        $this->_enableRenderer(new Api_Plugin_JsonPRenderer());
        return $this->_resultType(new Object(array('html' => $this->getResponse()->getBody())));
    }
    
    /**
     * Generic Starbar
     * @todo add to starbar table
     */
    public function genericAction () {
    }

    /**
     * Hello Music "Say.So Music Bar"
     */
    public function hellomusicAction () {
        
        // get Starbar passed via index or post-install-deliver
        // and assign it to the view
        $starbar = $this->_getStarbarObject();
        $user = $starbar->getUser();
        $this->view->assign('starbar', $starbar);
        $this->view->assign('user', $user);

		$facebookSocial = new User_Social();
		$facebookSocial->loadByUserIdAndProvider($user->id, 'facebook');
		$this->view->assign('facebook_social', $facebookSocial);

	    // render the view manually, we will pass it back in the JSON
        $this->render();
        
        // setup Hello Music specific data
        $starbar->setCssUrl('//' . BASE_DOMAIN . '/css/starbar-hellomusic.css');
        $starbar->setHtml($this->getResponse()->getBody());

        // return Starbar via JSON-P
        $this->_enableRenderer(new Api_Plugin_JsonPRenderer());
        return $this->_resultType($starbar);
    }
    
    /**
     * Make sure Starbar has been determined in index (or post-install-deliver)
     * @return Starbar
     */
    private function _getStarbarObject () {
        if ($this->starbar && $this->starbar instanceof Starbar && $this->starbar->hasId()) { 
            return $this->starbar;
        } else {
            throw new Exception('Remote starbar actions cannot be accessed directly. Use /starbar/remote with id or short_name.');
        }
    }
    
}
