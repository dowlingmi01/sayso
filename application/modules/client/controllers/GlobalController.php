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

class Client_GlobalController extends Api_GlobalController
{
	protected $_uuidCookieName = '';
	protected $_loggedInCookieName = '';
	protected $_uuidType;

	public function preDispatch() {
		
	}
	
	/**
	 * SIMULATED customer Home page
	 * - override in child controllers for customer
	 *   specific logic
	 */
	public function homeAction () {
		// this is accessed via the child controller (e.g. hellomusic)
		$this->view->assign(array(
			'uuid' => isset($_COOKIE[$this->_uuidCookieName]) ? $_COOKIE[$this->_uuidCookieName] : '',
			'uuidType' => $this->_uuidType,
			'userLoggedIn' => isset($_COOKIE[$this->_loggedInCookieName]) ? true : false,
			'loggedInCookieName' => $this->_loggedInCookieName
		));
		$this->renderScript('global/home.phtml');
	}
	
	/**
	 * SIMULATED customer Login (callback action)
	 * 
	 */
	public function loginAction () {
		$this->_enableRenderer(new Api_Plugin_JsonPRenderer());
		setcookie($this->_uuidCookieName, $this->uuid, mktime(0,0,0,12,31,2030), '/');
		setcookie($this->_loggedInCookieName, md5($this->uuid), mktime(0,0,0,12,31,2030), '/');
		// on reload, the following doesn't get used
		$clientData = array(
			'name' => strtolower($this->_request->getControllerName()),
			'uuid' => $this->uuid,
			'uuidType' => $this->_uuidType,
			'userLoggedIn' => 1
		);
		return $this->_resultType(new Object($clientData));
	}
	
	public function embedAction () {
		
	}
}

