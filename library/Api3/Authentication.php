<?php

class Api3_Authentication
{
	/**
	 *
	 * @var bool
	 */
	protected $_api_auth  = FALSE;

	/**
	 *
	 * @var bool
	 */
	protected $_action_auth  = FALSE;

/////////////////////////////////////////////////

	/**This needs to be developed
	 * Default placeholder for now
	 *
	 * overload this in the specific implementation
	 *
	 * @return boolean
	 */
	public function apiAuthentication()
	{
		$this->_api_auth = FALSE;
	}

	/**This needs to be developed
	 * Default placeholder for now
	 *
	 * overload this in the specific implementation
	 *
	 * @return boolean
	 */
	public function actionAuthentication($action)
	{
		$this->_action_auth = FALSE;
		return $this->_action_auth;
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

	/**returns an instance of the authentication class as
	 * determined by the submitted user type
	 *
	 * this must be called from the $Api3_Api context so that
	 * $this-> works as intended.
	 *
	 * @return \className|boolean
	 */
	public function getAuthentication()
	{
		$user_type = $this->getUserType();
		//check if file exists
		$className = $this->module_name . "Authentication_" . ucfirst($user_type) . "Controller";
		if (class_exists($className))
		{
			//load an instance of it
			return new $className;
		} else {
			$this->error->newError("auth_load_fail");
			return FALSE;
		}
	}
}