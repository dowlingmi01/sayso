#!/usr/bin/php -q
<?php
/**
 * App Install Monitor
 * 
 * The purpose of this service is to monitor and record all app
 * installations and to flush the IP/UA fields after x seconds
 * has elapsed since install began, thereby allowing other 
 * users to install who coincidentally have the same IP/UA combo
 * 
 * This service requires pcntl and posix extensions on PHP
 * and to be run as user 'root'
 * 
 * @author davidbjames
 */

// ------------------------------------------------------------------
// CLI Arguments

$runmode = array(
	'no-daemon' => false,
	'help' => false,
	'write-initd' => false,
);
 
// Scan command line attributes for allowed arguments
foreach ($argv as $k=>$arg) {
	if (substr($arg, 0, 2) == '--' && isset($runmode[substr($arg, 2)])) {
		$runmode[substr($arg, 2)] = true;
	}
}
 
// Help mode. Shows allowed argumentents and quit directly
if ($runmode['help'] == true) {
	echo 'Usage: '.$argv[0].' [runmode]' . "\n";
	echo 'Available runmodes:' . "\n";
	foreach ($runmode as $runmod=>$val) {
		echo ' --'.$runmod . "\n";
	}
	die();
}

// ------------------------------------------------------------------
// Include/autoload/Zend setup

date_default_timezone_set('UTC');

defined('APPLICATION_PATH')
	|| define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

defined('APPLICATION_ENV')
	|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

define('GLOBAL_LIBRARY_PATH', realpath(APPLICATION_PATH . '/../../library'));

set_include_path('.' . PATH_SEPARATOR . realpath(APPLICATION_PATH . '/../library') . PATH_SEPARATOR . GLOBAL_LIBRARY_PATH);

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
Api_Registry::set('config', $config);
 
error_reporting(E_ALL);

// ------------------------------------------------------------------
// Setup System Daemon

require_once 'System/Daemon.php'; 

$options = array(
	'appName' => 'SaysoInstallMonitor', // called Sayso* here because it creates directories in /var/run
	'appDir' => dirname(__FILE__),
	'appDescription' => 'Monitors installation of browser apps',
	'authorName' => 'David James',
	'authorEmail' => 'david@say.so',
	'usePEAR' => false,
	'logLocation' => dirname(__FILE__) . '/' . str_replace('php', 'log', basename(__FILE__)),
	'sysMaxExecutionTime' => '0',
	'sysMaxInputTime' => '0',
	'sysMemoryLimit' => '200M',
	'appRunAsGID' => 1000,
	'appRunAsUID' => 1000,
);
 
System_Daemon::setOptions($options);

// setup flag file. used to stop the service gracefully (instead of using kill)
// note: this has to be done before System_Daemon switches users
$flagFile = dirname(__FILE__) . '/Running-AppInstallMonitor';
file_put_contents($flagFile, '1');

// This program can also be run in the forground with runmode --no-daemon
if (!$runmode['no-daemon']) {
	// Spawn Daemon
	System_Daemon::start();
}

/**
 * With runmod --write-initd, this program can automatically write a
 * system startup file called: 'init.d'
 * This will make sure your daemon will be started on reboot
 */
if ($runmode['write-initd']) {
	if (($initd_location = System_Daemon::writeAutoRun()) === false) {
		System_Daemon::log(System_Daemon::LOG_NOTICE, 'unable to write init.d script');
	} else {
		System_Daemon::log(System_Daemon::LOG_INFO, 'sucessfully written startup script: ' . $initd_location);
	}
}

$clientSignatures = array();

// ------------------------------------------------------------------
// THE LOOP!

System_Daemon::log(System_Daemon::LOG_INFO, 'AppInstallMonitor started');

try {
	
	while (!System_Daemon::isDying()) {
		
		// lookup all external users that who have just done installs
		// and more than 120 seconds has elapsed 
		$externalUsers = Db_Pdo::fetchAll('SELECT * FROM external_user WHERE install_begin_time AND timestampdiff(SECOND, install_begin_time, now()) >= 120');
		
		if ($externalUsers) {
			
			foreach ($externalUsers as $externalUser) {
				// record the installation in external_user_install including IP/UA and begin time
				$sql = '
					INSERT INTO external_user_install 
						(id, external_user_id, token, ip_address, user_agent, begin_time, completed_time, created) 
					VALUES 
						(null, ?, ?, ?, ?, ?, now(), now())';
				Db_Pdo::execute($sql,
					$externalUser['id'],
					$externalUser['install_token'],
					$externalUser['install_ip_address'],
					$externalUser['install_user_agent'],
					$externalUser['install_begin_time']);
					
				// flush the IP/UA/begin time fields, thereby freeing up this for users with the same sig
				Db_Pdo::execute('UPDATE external_user SET install_ip_address = NULL, install_user_agent = NULL, install_begin_time = NULL WHERE id = ?', $externalUser['id']);
				
				// track each IP/UA combo and log duplicates
				$sig = md5($externalUser['install_ip_address'] . $externalUser['install_user_agent']);
				if (in_array($sig, $clientSignatures)) {
					System_Daemon::log(System_Daemon::LOG_INFO, 'Multiple installs detected for ' . $externalUser['install_ip_address'] . ' ' . $externalUser['install_user_agent']);
				} else {
					$clientSignatures[] = $sig; 
				}
			}
			System_Daemon::log(System_Daemon::LOG_INFO, count($externalUsers) . ' external user(s) flushed.');
		}
		
		// if flag file deleted, then gracefully stop service
		if (!file_exists($flagFile)) {
			break;
		}
		
		// run every 10 seconds
		System_Daemon::iterate(10);
	}
} catch (Exception $exception) {
	error_log('Sayso Install Monitor is down! With exception: ' . $exception->getMessage());
}

// cleanup flag file
if (file_exists($flagFile)) {
	unlink($flagFile);
}

System_Daemon::stop();






