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
        $currentRemoteAddress = getRemoteAddress();
        $devAddresses = array('djames' => '195.60.129.27', 'local' => '127.0.0.1');
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
        
        if ($request->getParam('bundle_of_joy')) {
            foreach (explode('^|^', $request->getParam('bundle_of_joy')) as $keyValue) {
                $parts = explode('^-^', $keyValue);
                $request->setParam($parts[0], $parts[1]);
            }
        }
        
        if ($currentModule === 'api') return;
        
        $userKey = $request->getParam(Api_Constant::USER_KEY);
        Api_UserSession::init($userKey);
        
        $layout = Zend_Layout::startMvc();
        
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