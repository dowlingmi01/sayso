<?php
/**
 * Standard index.php (using Library)
 * 
 * @author davidbjames
 */

// path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

define('GLOBAL_LIBRARY_PATH', realpath(APPLICATION_PATH . '/../../library'));

// ensure global library is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    GLOBAL_LIBRARY_PATH,
    get_include_path(),
)));

// setup autoloader
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();

// setup configuration
$config = new Zend_Config_Ini(
    APPLICATION_PATH . '/configs/application.ini', 
    APPLICATION_ENV,
    array('allowModifications' => true)
);
// setup registry (and load with config)
require_once 'Api/Registry.php';
Zend_Registry::setClassName('Api_Registry');
// from here forward use Api_Registry
Api_Registry::set('config', $config); 

// Create application, bootstrap, and run
$application = new Zend_Application(APPLICATION_ENV, $config);
$application->bootstrap()->run();