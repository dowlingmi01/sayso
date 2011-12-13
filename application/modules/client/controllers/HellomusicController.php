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

class Client_HellomusicController extends Client_GlobalController
{
	
	protected $_uuidCookieName = 'MyEmail';
	protected $_loggedInCookieName = 'CHOMPUID';
	protected $_uuidType = 'email';
	
	/**
	 * SIMULATED Hello Music Home page
	 * 
	 */
	public function homeAction () {
		$this->view->assign(array(
			'screenshot' => '/client/hellomusic/images/HelloMusicScreenShot.png'
		));
		return parent::homeAction();
	}
	
	/**
	 * AJAX loaded install overlay for Hello Music
	 * 
	 */
	public function installAction () {
		$this->render();
		$body = $this->getResponse()->getBody();
		$this->_enableRenderer(new Api_Plugin_JsonPRenderer());
		return $this->_resultType(new Object(array('html' => $body)));
	}

	
}

