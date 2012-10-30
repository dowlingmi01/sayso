<?php
class Client_Social
{
	public function processKeys( Zend_Controller_Request_Http $request ) {
	}
	public function getPostInstallURL() {
		$env = Registry::getPseudoEnvironmentName();
		if( $env === 'PROD' )
			return 'http://social.say.so';
		else
			return 'http://' . Registry::getConfig()->baseDomain . '/client/social/landing';
	}
}
