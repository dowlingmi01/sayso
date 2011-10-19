<?php
/**
 * Starbar actions in this controller are for local testing,
 * using an environment (via actions/views) that mimics the browser app.
 * Each view brings in the Remote equivalent via partial()
 *
 * @see RemoteController for actual Starbars
 * @author davidbjames
 */
require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Starbar_IndexController extends Api_GlobalController
{
    public function preDispatch() {
        // for the simulated app, we also have to simulate passing
        // a user_key from the "client". This only applies here for the
        // initial load. Other actions (ajax, etc) will still need to
        // pass user_key, which can be retreived via sayso.starbar.user.key
        if (!$this->user_id || !$this->user_key) {
            $this->user_id = 1;
            if (isset($_COOKIE['simulated_starbar_user_key'])) {
                $this->user_key = $_COOKIE['simulated_starbar_user_key'];
            } else {
                $this->user_key = md5(str_shuffle('abcdefghijklmnopqrstuvwxyz1234567890') . time());
                setcookie('simulated_starbar_user_key', $this->user_key);
            }
        } 
        Api_UserSession::getInstance($this->user_key)->setId($this->user_id);
        if (!in_array($this->_request->getActionName(), array('index', 'gaga'))) {
            // i.e. for everything based on Generic Starbar, use these includes
            $this->view->headLink()->appendStylesheet('/css/starbar-generic.css');
            $this->view->headLink()->appendStylesheet('/css/colorbox.css');
            $this->view->headScript()->appendFile('/js/starbar/jquery-1.6.1.min.js');
            $this->view->headScript()->appendFile('/js/starbar/jquery-ui-1.8.16.custom.min.js');
            $this->view->headScript()->appendFile('/js/starbar/jquery.jscrollpane.min.js');
            $this->view->headScript()->appendFile('/js/starbar/jquery.cookie.js');
            $this->view->headScript()->appendFile('/js/starbar/jquery.jeip.js');
            $this->view->headScript()->appendFile('/js/starbar/jquery.cycle.lite.js');
            $this->view->headScript()->appendFile('/js/starbar/jquery.colorbox-min.js');
            $this->view->headScript()->appendFile('/js/starbar/jquery.easyTooltip.js');
        }
    }

    public function indexAction () {

    }

    public function gagaAction()
    {
        $this->view->headLink()->appendStylesheet('/css/starbar-qualified.css');
        $this->view->headScript()->appendFile('/js/starbar/jquery-1.6.1.min.js');
    }

    public function genericAction () {
    }

    public function hellomusicAction () {
        $this->view->headLink()->appendStylesheet('/css/starbar-hellomusic.css');

    	$this->view->inlineScript()->appendFile('/js/starbar/starbar-new.js');

    	// Starbar
    	$starbar = new Starbar();
    	$starbar->loadDataByUniqueFields(array('short_name' => 'hellomusic'));
    	$starbar->setVisibility('sb_starbar-visOpen');
    	$starbar->setGame();
    	$this->view->starbar = $starbar;
    	
    	// User
    	$session = Api_UserSession::getInstance();
    	$user = $session->getUser();
		$this->view->user = $user;
		
		// Facebook Connection
		$facebookSocial = new User_Social();
		$facebookSocial->loadByUserIdAndProvider($user->id, 'facebook');
		$this->view->assign('facebook_social', $facebookSocial);

		// Gamer
		// make sure this user (and session) has a gaming user associated
		$gamer = Gamer::create($user->getId(), $starbar->getId());
		$session->setGamingUser($gamer);

		$this->_request->setParam('starbar_id', $starbar->getId());
        $game = Game_Starbar::getInstance();
        $game->checkin();
		$this->view->assign('game', $game);
	}
}

