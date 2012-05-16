<?php
class Client_Movie
{
	public function processKeys( Zend_Controller_Request_Http $request ) {
	}
	public function getPostInstallURL() {
		$env = Registry::getPseudoEnvironmentName();
		if( $env === 'PROD' )
			return 'http://www.say.so/movies';
		else
			return 'http://client.' . Registry::getConfig()->baseDomain . '/movie/home';
	}
}
