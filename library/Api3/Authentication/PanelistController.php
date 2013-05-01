<?php
/**
 * Application specific authentication functions and
 * overloads for user type panelists.
 *
 * @package Api3
 * @subpackage Authentication
 */

class Api3_Authentication_PanelistController  extends Api3_Authentication
{
	/**
	 * Anthentication for the panelist user type
	 *
	 *@param Api3_Request
	 * @param Api3_Error
	 */
	public function apiAuthentication($request, $error)
	{
		if (!isset($request->api_key) || !isset($request->api_user))
		{
			$error->newError("missing_params_panelist_auth");
			$this->_api_auth = FALSE;
		} else {
			//check user vs key
			$user_id = User_Key::validate($request->api_key);
			if ($request->api_user == $user_id)
			{
				$this->_api_auth = TRUE;
				$this->_setUserData($user_id, "user_id");
				$this->_setUserData($request->api_key, "user_key");

				//TODO: add current active starbar here perhaps
				//$this->_setUserData($request->api_key, "active_starbar");

				//get available starbars
				$this->_setUserData(User_State::getStarbarList($user_id), "starbars");
			} else {
				$this->_api_auth = FALSE;
			}
		}
	}

	/**
	 * This needs to be developed
	 * Default placeholder for now
	 *
	 * @return boolean
	 */
	public function actionAuthentication($action)
	{
		return$this->_action_auth = TRUE;
	}
}