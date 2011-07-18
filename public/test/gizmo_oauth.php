<?php

// ------------------
// setup Zend

set_include_path('/SpectrumDNA/library');
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();

// ------------------
// setup request

$consumer = new Zend_Oauth_Consumer(array(
    'callbackUrl' => 'http://local.sayso.com/test/gizmo_callback.php',
    'siteUrl' => 'http://restapi.surveygizmo.com/head/oauth',

    // http://local.sayso.com/test (gizmo_callback.php)
    'consumerKey' => '790a9b057a8055c31d8594f03237d72204e1c55de',
    'consumerSecret' => '9c56747c1505a21493f3ff13e2f94d00',
    'userAuthorizationUrl' => 'http://restapi.surveygizmo.com/head/oauth/authenticate' // since Zend uses "authorize"
));

// ------------------
// request token

$requestToken = $consumer->getRequestToken(); // Zend_Oauth_Token_Request

// ------------------
// save in session

Zend_Session::start();
$session = new Zend_Session_Namespace('gizmo');
$session->requestToken = serialize($requestToken);

// ------------------
// redirect
$consumer->redirect();

//$consumer->redirect(array('custom_pluginname' => 'My custom app name!'), null, new Zend_Oauth_Http_UserAuthorization($consumer));




// SurveyGizmo's response

/*
    Array
    (
        [oauth_callback_confirmed] => 1
        [oauth_token] => ed60cef297fb6f0b8ecbe00aa559726c04e1c3c4a
        [oauth_token_secret] => b0e7df7028eec72fcc7d34e9b78c4cc8
        [xoauth_token_ttl] => 3600{"result_ok":true,"0":"ed60cef297fb6f0b8ecbe00aa559726c04e1c3c4a"}
    )
*/

// oAuth in Four Steps:

// 1. get request token (w/key, secret) /oauth/request_token
// 2. redirect user (w/request token, callback url) /oauth/authorize
// 3. callback (w/request token, success)
// 4. get access token (w/request token) /oauth/access_token

// http://ec2-79-125-94-250.eu-west-1.compute.amazonaws.com (gizmo.php)
//    'consumerKey' => '203ee8d449b65df5aeb865d39b76847e04e1afbf6',
//    'consumerSecret' => '9dc8b7c9db262627ea2fbd976c975156'

