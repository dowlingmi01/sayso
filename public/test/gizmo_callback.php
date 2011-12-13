<?php

set_include_path('/SpectrumDNA/library');

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();

$consumer = new Zend_Oauth_Consumer(array(
	'callbackUrl' => 'http://local.sayso.com/test/gizmo_callback.php',
	'siteUrl' => 'http://restapi.surveygizmo.com/head/oauth',

	// http://local.sayso.com/test (gizmo_callback.php)
	'consumerKey' => '790a9b057a8055c31d8594f03237d72204e1c55de',
	'consumerSecret' => '9c56747c1505a21493f3ff13e2f94d00'
));

Zend_Session::start();
$session = new Zend_Session_Namespace('gizmo');

// ------------------
// get access token

$accessToken = $consumer->getAccessToken( // Zend_Oauth_Token_Access
	$_GET, 
	unserialize($session->requestToken)
); 

// ------------------
// store access token

// this should be stored in the db (assuming it is longterm)
$session->accessToken = serialize($accessToken);

// redirect to our gizmo test page

header('Location: gizmo_test.php');
exit();
