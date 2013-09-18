<?php

class MachinimaReload {
	public static function verifyEmail( $email, $digest ) {
		$url = sprintf('http://%s/ams_profile?email_id=%s&digest=%s',
			Registry::getConfig()->machinimareload->domain, $email, $digest);
		$client = new Zend_Http_Client($url);
		$response = $client->request();
		if($response->isSuccessful()) {
			$body = $response->getBody();
			$value = json_decode($body, true);
			if( is_array($value) && array_key_exists('username', $value) )
				return($value);
		}
		throw new Exception('Invalid machinimareload user');
	}
}