<?php 

/**
 * Game class for Starbars
 * 
 * General info about Game classes:
 * 
 * Abstracting the game via a separate "Game" class allows
 * fine-tuning the game, by (1) running multiple transactions per
 * action, (2) adding conditional logic based on the request object,
 * OR (3) whatever other logic you need to put in the method 
 * 
 * Method naming usually corresponds to the controller+action
 * where the transaction should be triggered. So for example,
 * Gaming controller with action testBigDoorAction could have
 * a method named gamingTestBigDoor. You can also name methods
 * arbitrarily, in cases where there is no direct correlation
 * between the controller/action and the transaction. The benefit 
 * of using the naming convention however is that you can than 
 * automate the game (e.g. via postDipatch) like so:
 * Game_Factory::create($gamer, $this->_request)->trigger()
 * 
 * About this particular class:
 * 
 * Aside from the transaction methods, this class also wraps:
 * - creation of the specific Game class, which is determined 
 *   by the developer.application record for the current Starbar.
 *   see static create() below for more information
 * - submitting the action including trapping/reporting exceptions
 *   and building the user profile to return in the response
 * 	 see submitAction() below
 * 
 * @author davidbjames
 *
 */
abstract class Game_Starbar extends Game_Abstract {

    const SHARE_POLL = 'poll';
    const SHARE_SURVEY = 'survey';
    const SHARE_STARBAR = 'starbar';
    const SHARE_PROMOS = 'promos';
    
	public function init() {
		$this->loadLevels();
		parent::init();
	}
    
    public function gamingTestBigDoor () {
        $this->submitAction('POLL_STANDARD');
    }
    
    public function install () {
        $this->submitAction('STARBAR_OPT_IN');
    }
    
    public function checkin () {
        $this->submitAction('STARBAR_CHECKIN');
    }
    
    public function completeSurvey (Survey $survey, Survey_UserMap $surveyUserMap = null) {
        
        switch ($survey->type) {
            case 'poll' :
                if ($survey->premium) {
                    $this->submitAction('POLL_PREMIUM');
                } else {
                    $this->submitAction('POLL_STANDARD');
                }
                break;
            case 'survey' :
            default :
                if ($survey->premium) {
                    $this->submitAction('SURVEY_PREMIUM');
                } else {
                    $this->submitAction('SURVEY_STANDARD');
                }
        }
    }
    
    public function disqualifySurvey (Survey $survey) {
        if ($survey->premium) {
            $this->submitAction('SURVEY_PREMIUM_DISQUALIFIED');
        } else {
            $this->submitAction('SURVEY_STANDARD_DISQUALIFIED');
        }
    }
    
    public function share ($type, $typeId = 0) {
        
        switch ($type) {
            case self::SHARE_POLL :
            case self::SHARE_SURVEY :
                if (!$typeId) {
                    throw new Api_Exception(Api_Error::create(Api_Error::GAMING_ERROR, 'Cannot award user for sharing survey/poll. Survey id missing from call and required in order to determine if standard or premium.'));
                }
                $survey = new Survey();
                $survey->loadData($typeId);
                if ($type === self::SHARE_POLL) {
                    if ($survey->premium) {
                        $this->submitAction('POLL_PREMIUM_SHARE');
                    } else {
                        $this->submitAction('POLL_STANDARD_SHARE');
                    }
                } else {
                    if ($survey->premium) {
                        $this->submitAction('SURVEY_PREMIUM_SHARE');
                    } else {
                        $this->submitAction('SURVEY_STANDARD_SHARE');
                    }
                }
                break;
            case self::SHARE_STARBAR :
                $this->submitAction('SHARE_STARBAR');
                break;
            case self::SHARE_PROMOS :
                $this->submitAction('SHARE_PROMOS');
                break;
            default :
                throw new Api_Exception(Api_Error::create(Api_Error::GAMING_ERROR, 'Wrong type (' . $type . ') supplied to Game_Starbar::share(). See Game_Starbar "SHARE" constants for allowed types.'));
        }
    }
    
    public function viewPromos () {
        $this->submitAction('PROMOS_VIEW');
    }
    
    public function completeProfile (User $user) {
        if ($user->username && $user->primary_email_id) {
            $this->submitAction('PROFILE_COMPLETE');
        }
    }
    
    public function associateSocialNetwork (User_Social $userSocial) {
        switch ($userSocial->provider) {
            case 'facebook' :
                $this->submitAction('FACEBOOK_ASSOCIATE');
                break;
            case 'twitter' :
                $this->submitAction('TWITTER_ASSOCIATE');
                break;
            default :
                throw new Api_Exception(Api_Error::create(Api_Error::GAMING_ERROR, 'Wrong or missing social user provider (' . $userSocial->provider . ') supplied to Game_Starbar::associateSocialNetwork().'));
                break;
        }
    }

    abstract public function getPurchaseCurrencyId ();
    
    /**
     * Override parent::submitAction to grab user profile with updated 
     * points/currencies *after* the transaction has fired and load that
     * onto the gaming user. Furthermore, attach this gaming user to
     * the request via 'custom' parameter. IF the request is already returning
     * a Gaming user, then this will be ignored.
     * 
     * @see Game_Abstract::submitAction()
     */
    public function submitAction ($actionId, $customAmount = 0) {
        
        try {
            
            if (!Game_Abstract::$_enabled) return false;
            parent::submitAction($actionId, $customAmount);
            $this->loadGamerProfile(); // get latest points after transaction
            
        } catch (Exception $exception) {
            
            self::_handleException($exception, $this->_request);
            
        }
    }
    
    /**
     * Override so we can attach the gamer profile to the request
     * 
     */
    public function loadGamerProfile ($forceReload = false) {
        parent::loadGamerProfile($forceReload);
        $this->_request->setParam(Api_AbstractController::GAME, $this);
    }
    
    /**
     * Create a new Starbar "Game" 
     * 
     * The game is determined from the developer authentication tables,
     * so the first thing is to authenticate the app via the auth_key
     * 
     * The auth key is determined by:
     * 1. $request auth_key
     * 2. $starbar auth_key
     * 	  a. via Starbar object
     * 	  b. via starbar_id (in $request)
     *    c. via short_name (in $request)
     *    d. via Starbar_<shortname>Controller
     *   
     * @param Gaming_User $gamer
     * @param Zend_Controller_Request_Http $request
     * @return Game_Starbar | NullObject
     */
    public static function create (Gaming_User $gamer, Zend_Controller_Request_Http $request, Starbar $starbar = null) {
        
        try {
            // all games (and therefore transactions) are determined via authentication tables
            // so we begin the process here of determining the authentication key, in one of:
            
            // 1. auth_key
            $authKey = $request->getParam(Api_Constant::AUTH_KEY);
            if (!$authKey) {
                // 2. Starbar->auth_key
                if ($starbar) {
                    // 2a. via Starbar object
                    $authKey = $starbar->auth_key;
                } else {
                    if (Registry::isRegistered('starbar')) {
                        $starbar = Registry::getStarbar();
                    } else {
                        $starbar = new Starbar();
                        $starbarId = $request->getParam('starbar_id');
                        $shortName = $request->getParam('short_name');
                        if ($starbarId) {
                            // 2b. via starbar_id
                            $starbar->loadData($starbarId);
                        } else if ($shortName) {
                            // 2c. via short_name
                            $starbar->loadDataByUniqueFields(array('short_name' => $shortName));
                        } else {
                            // 2d. via Starbar_<shortname>Controller
                            $shortName = strtolower($request->getControllerName());
                            $starbar->loadDataByUniqueFields(array('short_name' => $shortName));
                            if (!$starbar->hasId()) {
                                throw new Api_Exception(Api_Error::create(Api_Error::GAMING_ERROR, 'Could not determine Game in Game_Starbar::create(). See method for for more information.'));
                            }
                        }
                    }
                    $authKey = $starbar->auth_key;
                }
            }
            $request->setParam(Api_Constant::AUTH_KEY, $authKey);
            
            // app must be authorized in order to determine game name
            // (Game_Factory will handle this)
            $auth = Api_Auth::getInstance()->authorizeApp($authKey);
            
            return Game_Factory::create($gamer, $request);
            
        } catch (Exception $exception) {
            
            self::_handleException($exception, $request);
            
        }
        return new NullObject('Game_Starbar');
    }
    
    /**
     * Get the single Game instance for this request
     * 
     * @return Game_Starbar
     */
    public static function getInstance () {
        static $game = null;
        if (Game_Abstract::$_enabled) {
            if (!$game) {
                $request = Zend_Controller_Front::getInstance()->getRequest();
                $gamer = Api_UserSession::getInstance($request->getParam('user_key'))->getGamingUser();
        		$game = Game_Starbar::create($gamer, $request);
            }
            return $game;
        } else {
            return new NullObject('Game_Starbar');
        }
    }
    
    /**
     * Handle game exceptions
     * - in production, they should be supressed
     *   but also logged
     * - in development, they should bubble up
     * - see notes inline for more info how to control
     *   whether they show up or not in either env.
     * 
     * @param Exception $exception
     * @param Zend_Controller_Request_Http $request
     * @throws Exception
     */
    protected static function _handleException (Exception $exception, Zend_Controller_Request_Http $request) {
        
        $debugGame = $request->getParam('debug_game');
        // on local dev: throw game exceptions -- use debug_game=false to supress exceptions
        // on live: supress exceptions -- use debug_game=true to throw exceptions
        if (($debugGame || APPLICATION_ENV === 'development' || APPLICATION_ENV === 'sandbox') && $debugGame !== 'false') {
            throw $exception;
        }
        // because Api_Exception unregisters the renderer, we need to restore it here
        if ($exception instanceof Api_Exception) {
            $exception->restoreRenderer();
        }
        // log errors regardless
        Api_Error::log($exception, $request);
    }
}
