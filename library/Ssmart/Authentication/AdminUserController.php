<?php
/**
 * Application specific authentication functions and
 * overloads for admin type users.
 *
 * @package Ssmart
 * @subpackage Authentication
 */
class Ssmart_Authentication_AdminUserController  extends Ssmart_Authentication
{
	/**
	 * This needs to be developed
	 * Default placeholder for now
	 *
	 *@param Ssmart_Request
	 * @param Ssmart_Error
	 */
	public function apiAuthentication($request, $error)
	{
		$this->_api_auth = TRUE;
	}

	/**
	 * This needs to be developed
	 * Default placeholder for now
	 *
	 *
	 * @return boolean
	 */
	public function actionAuthentication($action)
	{
		return $this->_action_auth = TRUE;
	}
}