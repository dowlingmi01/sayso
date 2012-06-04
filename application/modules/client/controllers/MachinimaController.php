<?php
/**
 * Starbar actions in this controller are for local testing,
 * using an environment (via actions/views) that mimics the browser app.
 * Each view brings in the Remote equivalent via partial()
 *
 * @see RemoteController for actual Starbars
 * @author davidbjames
 */
require_once APPLICATION_PATH . '/modules/client/controllers/GlobalController.php';

class Client_MachinimaController extends Client_GlobalController
{
	
	protected $_uuidCookieName = 'MyEmail';
	protected $_loggedInCookieName = 'CHOMPUID';
	protected $_uuidType = 'hash';
	
	/**
	 * SIMULATED Hello Music Home page
	 * 
	 */
	public function homeAction () {
		$this->view->assign(array(
			'screenshot' => '/client/machinima/images/MachinimaScreenShot.png'
			, 'color' => 'black'
		));
		return parent::homeAction();
	}
	
	public function loginAction () {
		$this->_enableRenderer(new Api_Plugin_JsonPRenderer());
		setcookie($this->_uuidCookieName, $this->uuid, mktime(0,0,0,12,31,2030), '/');
		setrawcookie($this->_loggedInCookieName, 'NAME=xxxxxx&HMID='.md5($this->uuid).'==', mktime(0,0,0,12,31,2030), '/');
		return $this->_resultType(true);
	}
	
	public function logoutAction () {
		$this->_enableRenderer(new Api_Plugin_JsonPRenderer());
		setcookie($this->_loggedInCookieName, '', mktime(0, 0, 0, 1, 1, 2000), '/');
		return $this->_resultType(true);
	}
}
