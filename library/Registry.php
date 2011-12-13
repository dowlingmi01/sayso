<?php


class Registry extends Api_Registry
{
	/**
	 * @return Starbar | NullObject
	 */
	public static function getStarbar () {
		return Api_Registry::_get('starbar');
	}
	
	/**
	 * Get the environment variable name modified
	 * for external use (app install, naming, etc)
	 * 
	 * @return string
	 */
	public static function getPseudoEnvironmentName () {
		switch (APPLICATION_ENV) {
			case 'development' :
				$env = 'LOCAL'; break;
			case 'sandbox' :
				$env = 'DEV'; break;
			case 'testing' :
				$env = 'TEST'; break;
			case 'staging' :
				$env = 'STAGE'; break;
			case 'demo' :
				$env = 'DEMO'; break;
			case 'production' :
			default :
				$env = 'PROD'; break;
		}
		return $env;
	}
}

