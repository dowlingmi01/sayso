<?php

define('GLOBAL_LIBRARY_PATH', '/SpectrumDNA/library');

set_include_path(implode(PATH_SEPARATOR, array(
    GLOBAL_LIBRARY_PATH,
    get_include_path(),
)));

require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();

require_once 'Api/Autoloader.php';

$autoloader->unshiftAutoloader(Api_Autoloader::getInstance());

$config = array(
    'callbackUrl' => 'http://local.sayso.com/test/gizmo_callback.php',
    'siteUrl' => 'http://restapi.surveygizmo.com/head/oauth',

    // http://local.sayso.com/test (gizmo_callback.php)
    'consumerKey' => '790a9b057a8055c31d8594f03237d72204e1c55de',
    'consumerSecret' => '9c56747c1505a21493f3ff13e2f94d00'
);

Zend_Session::start();
$session = new Zend_Session_Namespace('gizmo');

// ------------------
// get (stored) access token

$accessToken = unserialize($session->accessToken);
/* @var $accessToken Zend_Oauth_Token_Access */

$client = $accessToken->getHttpClient($config);

// ------------------
// retrieve a survey

$uri = new HttpUri('https://restapi.surveygizmo.com/v1');
$uri->addSegment('survey/587100');

$client->setUri($uri);
$client->setMethod(Zend_Http_Client::GET);
$client->setParameterGet('_method', 'GET');
$client->setParameterGet('metaonly', 'false');
$response = $client->request();
$body = $response->getBody();

// ------------------
// show response

exit($client->getLastRequest());

header('Content-type: application/json');
echo $body;


// oauth_token=cac2a0059ecaa78c7b0ed99521f8b32204e1d72ef&oauth_token_secret=49c16b2564a2b06981815628cbe4d7f0