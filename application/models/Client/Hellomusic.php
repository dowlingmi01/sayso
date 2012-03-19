<?php
class Client_Hellomusic
{
	public function processKeys( Zend_Controller_Request_Http $request ) {
		$clientKeys = $request->getParam("client_keys");
		if( isset($clientKeys["CHOMPUID"]) && $clientKeys["CHOMPUID"]
			&& preg_match( "/HMID=(.*)==/", $clientKeys["CHOMPUID"], $matches ) ) {
			$request->setParam("client_uuid_type", "hash");
			$request->setParam("client_uuid", $matches[1]);
			$request->setParam("client_email", $clientKeys["MyEmail"]);
			$request->setParam("client_user_logged_in", true);
		}
	}
}
