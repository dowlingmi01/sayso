<?php
abstract class Client
{
	abstract public function processKeys( Zend_Controller_Request_Http $request );
	static public function getInstance( $client_name ) {
		$className = 'Client_' . ucwords($client_name);
		return new $className;
	}
}
