<?php

class Api3_Authentication
{
	/**
	 *
	 * @var Api3_Authentication
	 */
	private static $_instance;

	/**
	 *
	 * @var bool
	 */
	private $_api_auth  = FALSE;

	/**
	 *
	 * @var bool
	 */
	private $_action_auth  = FALSE;

/////////////////////////////////////////////////

	/**checks for api user and key then
	 * routes the api authentication calls
	 */
	public function authenticate($request, $error)
	{
		//check for api_user and api_key
		if (isset($request->api_user) && isset($request->api_key))
		{
			switch ($request->user_type) {
				case "admin" :
					$this->_apiAdminUserAuthentication();
					break;
				case "program" :
					$this->_apiProgramUserAuthentication();
					break;
				//no known user type. reset all authentications
				default :
					$this->_action_auth = FALSE;
					$this->_api_auth = FALSE;
					$error->newError("auth_invalid_user_type");
			}
		} else {
			$error->newError("missing_user_credentials");
		}
	}

	/**This needs to be developed
	 * Default placeholder for now
	 *
	 * @return boolean
	 */
	private function _apiAdminUserAuthentication()
	{
		$this->_api_auth = TRUE; //default placeholder
		//$this->_api_auth = FALSE; //default placeholder
		//TODO: authenticate for admin access
	}

	/**This needs to be developed
	 * Default placeholder for now
	 *
	 * @return boolean
	 */
	private function _apiProgramUserAuthentication()
	{
		$this->_api_auth = TRUE; //default placeholder
		//$this->_api_auth = FALSE; //default placeholder
		//TODO: authenticate for program access
	}

	/**This needs to be developed
	 * Default placeholder for now
	 *
	 * @return boolean
	 */
	public function actionAuthentication($action)
	{
		$this->_action_auth = TRUE; //default placeholder
		//$this->_action_auth = FALSE; //default placeholder
		return $this->_action_auth;
		//TODO: authenticate for action access
	}

	/**returns the private property value of the requested
	 * authnetication call
	 *
	 * @param bool $isActionStatus
	 * @return string
	 */
	public function getAuthStatus($isActionStatus = FALSE)
	{
		if ($isActionStatus)
		{
			return $this->_action_auth;
		} else {
			return $this->_api_auth;
		}
	}

	/**resets the authentication for actions
	 *
	 */
	public function resetActionAuth()
	{
		$this->_action_auth = FALSE;
	}
}