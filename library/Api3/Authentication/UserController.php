<?php

class Api3_Authentication_UserController  extends Api3_Authentication
{
	/**This needs to be developed
	 * Default placeholder for now
	 *
	 *@param Api3_Request
	 * @param Api3_Error
	 * @return boolean
	 */
	public function apiAuthentication($request, $error)
	{
		if (!isset($request->api_key) || !isset($request->api_user))
		{
			$error->newError("missing_params_user_auth");
			$this->_api_auth = FALSE;
		} else {
			$this->_api_auth = TRUE;
			$this->user_id = $request->api_user;
		}
	}

	/**This needs to be developed
	 * Default placeholder for now
	 *
	 *
	 * @return boolean
	 */
	public function actionAuthentication($action)
	{
		$this->_action_auth = TRUE;
		return $this->_action_auth;
	}
}