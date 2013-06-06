<?php
/**
 * Application specific authentication functions and
 * overloads for user type panelists.
 *
 * @package Ssmart
 * @subpackage Authentication
 */

class Ssmart_Authentication_PanelistController  extends Ssmart_Authentication
{
	/**
	 * Anthentication for the panelist user type
	 *
	 * NOTE: $request->api_user is a session_id
	 *
	 *@param Ssmart_Request
	 * @param Ssmart_Error
	 */
	public function apiAuthentication($request, $error)
	{
		if (!isset($request->api_key) || !isset($request->api_user))
		{
			$error->newError("missing_params_panelist_auth");
			$this->_api_auth = FALSE;
		} else {
			//check for ip ban
			if (!User::isIpBanned())
			{
				//check user vs key
				$user_id = User_Session::validate($request->api_user, $request->api_key);
				if ($user_id)
				{
					$this->_api_auth = TRUE;
					$this->_setUserData($user_id, "user_id");
					$this->_setUserData($request->api_key, "session_key");
					$this->_setUserData($request->api_user, "session_id");

					//TODO: add current active starbar here perhaps
					//$this->_setUserData($request->api_key, "active_starbar");

					//get available starbars
					$this->_setUserData(User_State::getStarbarList($user_id), "starbars");
				} else {
					$this->_api_auth = FALSE;
				}
			} else {
				$this->_api_auth = FALSE;
			}
		}
	}

	/**
	 * Provides necessary checks on every action call.
	 * Can check the session validity,
	 * whether the ip is banned and
	 * whether the user has permission to the called action
	 * and anything else that needs to be checked on each call
	 *
	 * @param string
	 */
	public function actionAuthentication($action)
	{
		//check for ip ban
		if (!User::isIpBanned())
		{
			//check for active session
			$sessionCheck = User_Session::checkSession($this->userData->session_id);
			if ($sessionCheck)
			{
				is_string($sessionCheck) ? $this->userData->new_user_key = $sessionCheck : "";
				$this->_action_auth = TRUE;
			} else {
				$this->_action_auth = FALSE;
			}
		} else {
			$this->_action_auth = FALSE;
		}
	}
}