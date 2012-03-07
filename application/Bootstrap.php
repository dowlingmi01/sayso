<?php
require_once 'App/Bootstrap.php';
/**
 * Sayso bootstrap
 * - used for initializing/setting up the framework (including
 *   all apps/modules contained within)
 *
 * @author davidbjames
 *
 */
class Bootstrap extends App_Bootstrap
{
	public function initApp () {

		App_Bootstrap::initApp();

		Record::$defaultModifiedColumn = 'modified';
		Api_UserSession::setup('User', '', 'Gamer');
		//Api_UserSession::$regenerateMissingSessionId = true;

		Game_Abstract::$_enabled = true;

		// only log if used by a developer
		$remoteAddress = $_SERVER['REMOTE_ADDR'];
		$devAddresses = array(
			'195.60.129.27' => 'david', '127.0.0.1' => 'local', '98.154.229.46' => 'matt',
			'24.2.88.78' => 'jim', '72.10.153.136' => 'hamza'
		);

		if (array_key_exists($remoteAddress, $devAddresses)) {

			$apiLog = new Zend_Log();
			$apiLogWriter = new Zend_Log_Writer_Stream(realpath(APPLICATION_PATH . '/../log/api.log'));
			$apiLog->addWriter($apiLogWriter);
			Api_Registry::set('log', $apiLog);

		}

		Zend_Controller_Front::getInstance()->registerPlugin(new BootstrapPlugin($this), 1);
	}

	/**
	 * Setup bootstrap resource returning a usable link
	 * to table holding session data
	 * - this method is now a "callback style" init - first must determine module(s)
	 *   see BootstrapPlugin below
	 *
	 * @return Zend_Session_SaveHandler_DbTable
	 * @author alecksmart
	 */
	public function _initDbSessionHandler()
	{
		// Get values we supplied in application.ini
		$options	= $this->getOptions();
		if(!$options['sessionDbHandler']['on'])
		{
			return false;
		}
		$db		 = Zend_Db::factory('Pdo_Mysql', array(
			'host'			=> $options['sessionDbHandler']['host'],
			'username'		=> $options['sessionDbHandler']['username'],
			'password'		=> $options['sessionDbHandler']['password'],
			'dbname'		=> $options['sessionDbHandler']['dbname'])
		);
		$config	 = array(
			'name'		   => 'session',
			'primary'		=> 'id',
			'modifiedColumn' => 'modified',
			'dataColumn'	 => 'data',
			'lifetimeColumn' => 'lifetime',
			'db'			 => $db
		);

		$dbSessionHandler = false;

		// Try to iniialize db conncetion
		// and return the created handler
		try {
			$dbSessionHandler = new Zend_Session_SaveHandler_DbTable($config);
		}
		catch (Exception $e) {
			return false;
		}

		// @todo this is hackish, intent is that sessions are permanent (no gc)
		$dbSessionHandler->setLifetime(31500000);  // 1 year
		return $dbSessionHandler;
	}

	/**
	 * Custom router for all calls from system command line
	 *
	 * @return bool CLI route in effect
	 * @author alecksmart
	 */
	protected function _initCli()
	{
		if (PHP_SAPI != 'cli')
		{
			return false;
		}
		require APPLICATION_PATH . '/models/Task/Router/Cli.php';
		$this->bootstrap('frontcontroller');
		$front = $this->getResource('frontcontroller');
		$front->setRouter(new Task_Router_Cli());
		$front->setRequest(new Zend_Controller_Request_Simple());
		return true;
	}
	
	/* Don't use Api_Auth */
	public function setupAuthPlugin (Zend_Controller_Request_Abstract $request) {
	}
}

/**
 * Bootstrap plugin to enable add'l setup that can only be
 * done AFTER a certain point in the front controller life cycle.
 * For example, in routeShutdown we set the layout path to the module
 * directory based on the requested module
 *
 * @author davidbjames
 *
 */
class BootstrapPlugin extends Zend_Controller_Plugin_Abstract
{
	/**
	 * @var Bootstrap
	 */
	protected $_bootstrap = null;

	public function __construct(Bootstrap $bootstrap) {
		$this->_bootstrap = $bootstrap;
	}

	public function routeShutdown(Zend_Controller_Request_Abstract $request) {

		// Are we using a command line?
		if (PHP_SAPI == 'cli'){
			return;
		}

		if (strpos($_SERVER['SERVER_NAME'], 'client.') === 0) {
			$request->setModuleName('client');
		}

		$currentModule = strtolower($request->getModuleName());
		$currentController = strtolower($request->getControllerName());
		$currentAction = strtolower($request->getActionName());

		/*
		* bundle_of_joy is the variable sent to and from SurveyGizmo.
		* It is a 'manually' serialized list of variables and values, where ^|^ seperates variables
		* from each other, and ^-^ seperates variable names from variable values. Example:
		* GET version: ?user_id=1&user_key=123&auth_key=abc
		* bundle_of_joy version: ?bundle_of_joy=user_id^-^1^|^user_key^-^123^|^auth_key^-^abc
		*/
		if ($request->getParam('bundle_of_joy')) {
			foreach (explode('^|^', $request->getParam('bundle_of_joy')) as $keyValue) {
				$parts = explode('^-^', $keyValue);
				$request->setParam($parts[0], $parts[1]);
			}
		}

		if( !$request->getParam(Api_Constant::USER_KEY) && isset($_COOKIE[Api_Constant::USER_KEY])) {
			$request->setParam(Api_Constant::USER_KEY, $_COOKIE[Api_Constant::USER_KEY]);
			if( !$request->getParam(Api_Constant::USER_ID) && isset($_COOKIE[Api_Constant::USER_ID]))
				$request->setParam(Api_Constant::USER_ID, $_COOKIE[Api_Constant::USER_ID]);
		}
		
		// If there is a valid user_key, retrieve the corresponding user_id.
		// Otherwise, disallow any user_id and user_key parameters
		
		if( $token = $request->getParam(Api_Constant::USER_KEY) ) {
			$user_id = User_Key::validate($token);
			$request->setParam(Api_Constant::USER_ID, $user_id);
			if(!$user_id)
				$request->setParam(Api_Constant::USER_KEY, null);
		} else if( $request->getParam(Api_Constant::USER_ID) )
			$request->setParam(Api_Constant::USER_ID, null);
					
		if ($currentModule === 'api') return;

		$layout = Zend_Layout::startMvc();
		$view = $layout->getView();
		$view->partialLoop()->setObjectKey('model');

		switch ($currentModule) {
			case 'default' :
				$layout->setLayoutPath(APPLICATION_PATH . '/layouts/scripts');
				break;
			case 'admin' :
			case 'starbar' :
			case 'client' :
			default :
				$layout->setLayoutPath(APPLICATION_PATH . '/modules/' . $currentModule . '/layouts/scripts');
				break;
		}
	}
}
