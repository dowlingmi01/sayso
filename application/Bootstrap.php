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
        parent::initApp();
        Record::$defaultModifiedColumn = 'modified';
        // API logging will be necessary since requests
        // are coming from mobile, it's hard to see what's going on
        $apiLog = new Zend_Log();
        $apiLogWriter = new Zend_Log_Writer_Stream(realpath(APPLICATION_PATH . '/../log/api.log'));
        $apiLog->addWriter($apiLogWriter);
        
        // only log if used by a developer
        $currentRemoteAddress = $_SERVER['REMOTE_ADDR'];
        $devAddresses = array('djames' => '195.60.129.27', 'local' => '127.0.0.1', 'dev' => '174.129.49.244');
        if (!in_array($currentRemoteAddress, $devAddresses)) {
            $filterSupress = new Zend_Log_Filter_Suppress();
            $filterSupress->suppress(true);
            $apiLogWriter->addFilter($filterSupress);
        }
        
        // all dev logging begins with the IP of the client
        $apiLog->log(
            PHP_EOL. PHP_EOL . 'IP: ' . $currentRemoteAddress . 
        	' ' . str_repeat('-', 100) . 
            PHP_EOL, Zend_Log::INFO
        );
        
        Api_Registry::set('log', $apiLog);
        Zend_Controller_Front::getInstance()->registerPlugin(new BootstrapPlugin());
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
    public function routeShutdown(Zend_Controller_Request_Abstract $request) {
        $currentModule = strtolower($request->getModuleName());
        
        // Prevent PHP from creating PHPSESSID cookie so that we don't 
        // inadvertently overwrite a user's session on a site that uses PHP!
        // Instead, we will manage keeping the session alive via kobj.net cookie
        // and pass that as user_key.
        if ($currentModule === 'api' || $currentModule === 'starbar') {
            ini_set('session.use_only_cookies', '0');
            ini_set('session.use_cookies', '0');
            ini_set('session.use_trans_sid', '0');
        }
        
        // make sure api requests has user_key
        if ($currentModule === 'api' && (!$request->getParam('user_key') || $request->getParam('user_key') === 'undefined')) {
            $message = 'SaySo API requires user_key in every request.';
            if ($request->getParam('user_key') === 'undefined') $message .= ' (user_key === "undefined")'; // probably need to delete kobj.net cookie
            $message .= ' URI: ' . $_SERVER['REQUEST_URI'];
            throw new Exception($message);
        }
            
        if ($currentModule === 'api') return;
        
        $userKey = $request->getParam(Api_Constant::USER_KEY);
        if ($userKey) {
            Api_UserSession::init($userKey);
        }
        
        $layout = Zend_Layout::startMvc();
        
        $cache = Zend_Cache::factory('Core', 'File', array('automatic_serialization' => true, 'lifetime' => 3600), array('cache_dir' => CACHE_PATH));
		Api_Registry::set('cache', $cache);

        
        switch ($currentModule) {
            case 'default' :
                $layout->setLayoutPath(APPLICATION_PATH . '/layouts/scripts');
                break;
            case 'admin' :
            case 'starbar' :
            default :
                $layout->setLayoutPath(APPLICATION_PATH . '/modules/' . $currentModule . '/layouts/scripts');
                break;
        }
    }
}