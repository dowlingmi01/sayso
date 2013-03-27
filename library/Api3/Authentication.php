<?php
/**
 * <p>Sets the default structure and provides some common
 * authentication related functions for passing around the api.</p>
 *
 * @package Api3
 */
class Api3_Authentication
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
	 * User id
	 *
	 * @var int
	 */
	public $user_id ;

/////////////////////////////////////////////////

	/**
	 * This needs to be developed
	 * Default placeholder for now
	 *
	 * Overload this in the specific implementation
	 *
	 * @return boolean
	 */
	public function apiAuthentication()
	{
		$this->_api_auth = FALSE;
	}

	/**
	 * This needs to be developed
	 * Default placeholder for now
	 *
	 * Overload this in the specific implementation
	 *
	 * @return boolean
	 */
	public function actionAuthentication()
	{
		$this->_action_auth = FALSE;
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
	 * <p>This must be called from the $Api3_Api context so that
	 * $this-> works as intended.</p>
	 *
	 * @return \className|boolean
	 */
	public function getAuthentication()
	{
		//_rquest object is private....
		$user_type = $this->getUserType();
		$moduleName = $this->getModuleName();
		//check if file exists
		$className = $moduleName . "Authentication_" . ucfirst($user_type) . "Controller";
		if (class_exists($className))
		{
			//load an instance of it
			return new $className;
		} else {
			$this->_error->newError("auth_load_fail");
			return FALSE;
		}
	}
}