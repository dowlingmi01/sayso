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
	public $user_data = null;

	/**
	 * Shared user type
	 *
	 * @var \string Holds the user type (automatically filled after authentication)
	 */
	public $user_type = "unauthenticated";

/////////////////////////////////////////////////

	public function __construct($userType) {
		$this->user_data = new stdClass();
		$this->user_type = $userType;
	}


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
	 * @param string userType
	 * @param string moduleName
	 *
	 * @return className|boolean
	 */
	static public function getAuthentication($userType, $moduleName)
	{
		//check if file exists
		$className = $moduleName . "_Authentication_" . ucfirst($userType) . "Controller";
		if (class_exists($className))
		{
			//load an instance of it
			return new $className($userType);
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
				if ($nodeName) {
					if (!property_exists($this, 'user_data'))
						$this->user_data = new stdClass();
					if (!property_exists($this->user_data, $nodeName))
						$this->user_data->$nodeName = [];
					$this->user_data->{$nodeName}[$key] = $value;
				} else {
					$this->user_data->$key = $value;
				}
			}
		} else
			$this->user_data->$nodeName = $data;
	}

}