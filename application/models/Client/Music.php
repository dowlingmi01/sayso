<?php
class Client_Music
{
	public function processKeys( Zend_Controller_Request_Http $request ) {
	}
	public function getPostInstallURL() {
		$env = Registry::getPseudoEnvironmentName();
		if( $env === 'PROD' )
			return 'http://music.say.so';
		else
			return 'http://' . Registry::getConfig()->baseDomain . '/client/music/landing';
	}
}
