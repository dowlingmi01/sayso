<?php
class Client_Movie
{
	public function processKeys( Zend_Controller_Request_Http $request ) {
	}
	public function getPostInstallURL() {
		$env = Registry::getPseudoEnvironmentName();
		if( $env === 'PROD' )
			return 'http://movie.say.so';
		else
			return 'http://' . Registry::getConfig()->baseDomain . '/client/movie/landing';
	}
}
