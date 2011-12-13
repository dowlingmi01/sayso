<?php

require_once APPLICATION_PATH . '/controllers/GlobalController.php';

class UserController extends GlobalController
{

	public function indexAction()
	{
		
	}
	
	public function registerAction () {
		$this->_disableLayout(); // ajax loaded
	}

	public function loginAction () {
		$this->_disableLayout(); // ajax loaded
	}
	
	public function logoutAction ()
	{
		Api_UserSession::logout();
		$this->_redirect($this->return_url);
	}
}

