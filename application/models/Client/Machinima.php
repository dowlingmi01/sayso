<?php
class Client_Machinima
{
	public function processKeys( Zend_Controller_Request_Http $request ) {
	}
	public function getPostInstallURL() {
		$env = Registry::getPseudoEnvironmentName();
		if( $env === 'PROD' )
			return 'http://www.machinima.com/';
		else
			return 'http://' . Registry::getConfig()->baseDomain . '/client/machinima/landing';
	}
}
