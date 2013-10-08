<?php

class MachinimaReload {
	public static function verifyEmail( $email, $digest ) {
		$url = sprintf('http://%s/ams_profile?email_id=%s&digest=%s&client_id=%s',
			Registry::getConfig()->machinimareload->domain, $email, $digest, Registry::getConfig()->machinimareload->client_id);
		$client = new Zend_Http_Client($url);
		$response = $client->request();
		if($response->isSuccessful()) {
			$body = $response->getBody();
			$value = json_decode($body, true);
			if( is_array($value) && array_key_exists('username', $value) ) {
				$value['machinimareload_digest'] = $digest;
				$value['birthdate'] = $value['birthday'];
				$value['username'] = $value['first_name'] . ' ' . $value['last_name'];
				return($value);
			}
		}
		throw new Exception('Invalid machinimareload user');
	}
}