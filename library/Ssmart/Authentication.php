<?php
/**
 * <p>Sets the default structure and provides some common
 * authentication related functions for passing around the api.</p>
 *
 * @package Ssmart
 * @subpackage Authentication
 */
class Ssmart_Authentication
{
	/**
	 * Whether the call is authorized to access the api.
	 *
	 * @var bool
	 */
	protected $_api_auth  = FALSE;

	/**
	 * Whether the action is authorized to access the requested action
	 *
	 * @var bool
	 */
	protected $_action_auth  = FALSE;

	/**
	 * Shared user data
	 *
	 * @var \stdClass Holds shared data on the user after authentication
	 */
	public $user_data ;

/////////////////////////////////////////////////

	/**
	 * Status accessor
	 *
	 * @param bool $isActionStatus
	 * @return string
	 */
	public function getAuthStatus($isActionStatus = FALSE)
	{
		if ($isActionStatus)
			return $this->_action_auth;
		else
			return $this->_api_auth;
	}

	/**
	 * Resets the authentication for actions
	 *
	 */
	public function resetActionAuth()
	{
		$this->_action_auth = FALSE;
	}

	/**
	 * Returns an instance of the authentication class as
	 * determined by the submitted user type
	 *
	 * <p>This must be called from the $Ssmart_Api context so that
	 * $this-> works as intended.</p>
	 *
	 * @return \className|boolean
	 */
	public function getAuthentication($userType, $moduleName)
	{
		//_request object is private....
		$user_type = $userType;
		$moduleName = $moduleName;
		//check if file exists
		$className = $moduleName . "Authentication_" . ucfirst($user_type) . "Controller";
		if (class_exists($className))
		{
			//load an instance of it
			return new $className;
		} else {
			return FALSE;
		}
	}

	/**
	 * Sets authentication parameters
	 *
	 * @param string $data
	 * @param mixed $nodeName
	 */
	protected function _setUserData($data, $nodeName = NULL)
	{
		if (is_array($data) || is_object($data))
		{
			foreach ($data as $key => $value)
			{
				if ($nodeName)
					$this->userData->$nodeName->$key = $value;
				else
					$this->userData->$key = $value;
			}
		} else
			$this->userData->$nodeName = $data;
	}

}