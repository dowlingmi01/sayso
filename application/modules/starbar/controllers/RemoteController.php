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
class Starbar_RemoteController extends Api_AbstractController
{
    private static $_errorControllerSet = false;
    
    public function preDispatch() {
        if (!self::$_errorControllerSet) {
            Zend_Controller_Front::getInstance()
                ->getPlugin('Zend_Controller_Plugin_ErrorHandler')
                ->setErrorHandlerModule(Api_Bootstrap::$moduleName);
            self::$_errorControllerSet = true;
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
            
            $this->_validateRequiredParameters(array('user_id', 'auth_key'));
            Api_Auth::getInstance()->authorizeApp($this->auth_key);
            
            // since the starbar id is known at this point, we can safely delete 
            // the user IP/UserAgent which will protect us from conflicts during 
            // installation/authentication. (scenario: multiple users sharing an IP
            // and who also happen to have the exact same browser and version number)
            // ALSO, only delete if install_begin_time is more than 1 minute ago
            // so that we honor multi-tab/slow installing
            // @todo figure out a better approach so this doesn't have to be run
            // for every request (me wishes it could be done asyncronously)
            // @todo save IP/UA for statistical purposes
            
            $externalUserData = Db_Pdo::fetch('SELECT * FROM external_user WHERE user_id = ?', $this->user_id);
            if ($externalUserData['install_ip_address']) {
                $install = new External_UserInstall();
                $install->external_user_id = $externalUserData['id'];
                $install->token = $externalUserData['install_token'];
                $install->ip_address = $externalUserData['install_ip_address'];
                $install->user_agent = $externalUserData['install_user_agent'];
                $install->begin_time = $externalUserData['install_begin_time'];
                $install->completed_time = new Zend_Db_Expr('now()');
                $install->save();
                
                Db_Pdo::execute('UPDATE external_user SET install_ip_address = NULL, install_user_agent = NULL, install_begin_time = NULL WHERE id = ? AND timestampdiff(SECOND, install_begin_time, now()) >= 30', $externalUserData['id']);
            }
                    
            if ($this->starbar_id) {
                
                $starbar->loadData($this->starbar_id);
            } else {
                $starbar->loadDataByUniqueFields(array('short_name' => $this->short_name));
            }
            
            if ($this->visibility) {
                $starbar->setVisibility($this->visibility);
            }
            
            if ($this->client_uuid) { 
                // we are on the customer's web site (must be if these params are present)
                // also client_name and client_uuid_type
                
                // so verify that the user id matches the uuid
                // if NOT, then switch users
                if ($externalUserData['uuid'] !== $this->client_uuid) {
                    // user change! (on same browser/computer)
                    // create/update external user
                    $externalUser = new External_User();
                    $externalUser->uuid = $this->client_uuid; // unique
                    $externalUser->uuid_type = $this->client_uuid_type;
                    $externalUser->starbar_id = $starbar->getId(); // unique
                    $externalUser->install_ip_address = $_SERVER['REMOTE_ADDR'];
                    $externalUser->install_user_agent = $_SERVER['HTTP_USER_AGENT'];
                    $externalUser->save(); // <-- inserts/updates based on uniques
                    
                    if ($starbar->short_name !== $this->client_name) {
                        // customer site change!
                        // @todo handle this scenario
                    }
                    return $this->_forward(
                        'post-install-deliver', 
                        null, 
                        null, 
                        array('external_user' => $externalUser)
                    );
                    
                }
            }
            $user = new User();
            $user->loadData($this->user_id);
            $starbar->setUser($user);
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
        if (!$this->uuid) return; 
        
        // validate
        $this->_validateRequiredParameters(array('auth_key', 'name', 'uuid', 'uuid_type', 'install_token'));
        
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
            if ($externalUserTest['uuid'] === $this->uuid) { 
                $this->install_token = $_COOKIE['starbar_setup_install_token'];
            } 
            // else
            // this is a new user altogether so use the new install token
            // and create a new user
        }
        
        // determine Starbar from short name
        $starbarData = Db_Pdo::fetch('SELECT * FROM starbar WHERE short_name = ?', $this->name);
        
        $externalUser = new External_User();
        
        $externalUser->uuid = $this->uuid; // unique
        $externalUser->uuid_type = $this->uuid_type;
        $externalUser->starbar_id = $starbarData['id']; // unique
        $externalUser->install_token = $this->install_token;
        $externalUser->save();
        
        // set cookies (to be retreived in post-install)
        setcookie('starbar_setup_auth_key', $this->auth_key);
        setcookie('starbar_setup_external_user_id', $externalUser->getId());
        setcookie('starbar_setup_install_token', $this->install_token);
        
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
                throw new Api_Exception(Api_Error::create(Api_Error::APPLICATION_ERROR, 'Install token does not match for this client. Should be: ' . $externalUser->install_token . '. Instead it\'s: ' . $_COOKIE['starbar_setup_install_token']));
            }
            
            // save IP / user agent to external user
        
            $externalUser->install_ip_address = $ipAddress;
            $externalUser->install_user_agent = $userAgent;
            $externalUser->save();
            
            Db_Pdo::execute('UPDATE external_user SET install_begin_time = now() WHERE id = ? AND install_begin_time IS NULL', $externalUser->getId());
        
            
        } else if ($this->client_uuid) { // on customer site 
            
            Api_Registry::getLogger()->log('On customer site / no cookies', Zend_Log::INFO);
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
        
        if (!$results) {
            throw new Api_Exception(Api_Error::create(Api_Error::APPLICATION_ERROR, 'Attempt to lookup external user from IP address (' . $ipAddress . ') and user agent (' . $userAgent . ') failed. Cannot determine Starbar.'));
        }
        if (count($results) > 1) {
            throw new Api_Exception(Api_Error::create(Api_Error::APPLICATION_ERROR, 'More than one external user with the same IP address and user agent! IP: ' . $ipAddress . ', user agent: ' . $userAgent . ' for ' . count($results) . ' users'));
        }
            
        // External user
        
        $externalUser = new External_User();
        $externalUser->setData($results[0]);
    
        externalUserExists:
        
        
        // User
        
        $user = new User();
        
        // if user id already exists on external user, then use that
        
        if ($externalUser->user_id) {
            $user->setId($externalUser->user_id);
        } else { // .. otherwise create a new one
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
        
        // start the session
        
        $userSession = Api_UserSession::getInstance();
        // set the user id on the session
        $userSession->setId($user->getId());
        // set the key on the user object so it is available for client-apps
        $user->setKey($userSession->getKey());
        
        // Starbar
    
        $starbar = new Starbar();
        $starbar->loadData($externalUser->starbar_id);  
        
        $starbar->setUser($user); // <-- agreggate user to starbar
        
        // Map Starbar <-> User
        
        $starbarUserMap = new Starbar_UserMap();
        $starbarUserMap->user_id = $user->getId();
        $starbarUserMap->starbar_id = $starbar->getId();
        $starbarUserMap->active = 1;
        $starbarUserMap->save();
        
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
     * Hello Music "BeatBar"
     */
    public function hellomusicAction () {
        
        // get Starbar passed via index or post-install-deliver
        // and assign it to the view
        $starbar = $this->_getStarbarObject();
        $this->view->assign('starbar', $starbar);
        $this->view->assign('user', $starbar->getUser());
        
        // render the view manually, we will pass it back in the JSON
        $this->render();
        
        // setup Hello Music specific data
        $starbar->setApiAuthKey(Api_Registry::getConfig()->api->helloMusic->authKey);
        $starbar->setCssUrl('http://' . BASE_DOMAIN . '/css/starbar-hellomusic.css');
        $starbar->setHtml($this->getResponse()->getBody());

        // return Starbar via JSON-P
        $this->_enableRenderer(new Api_Plugin_JsonPRenderer());
        return $this->_resultType($starbar);
    }
    
    /**
     * Make sure Starbar has been determined in index
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

