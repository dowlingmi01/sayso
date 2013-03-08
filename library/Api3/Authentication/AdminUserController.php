<?php

class Api3_Authentication_AdminUserController  extends Api3_Authentication
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
		$this->_api_auth = TRUE;
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