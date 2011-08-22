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
        if ($currentModule === 'api') return;
        
        Api_UserSession::init();
        
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