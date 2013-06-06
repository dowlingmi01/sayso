<?php
/**
 * <p>No authentication is really needed for public endpoints. So we force them to TRUE.</p>
 *
 * @package Ssmart
 * @subpackage Authentication
 */

class Ssmart_Authentication_PublicController  extends Ssmart_Authentication
{
	/**
	 * Anthentication for the public user type
	 *
	 *@param Ssmart_Request
	 * @param Ssmart_Error
	 */
	public function apiAuthentication($request, $error)
	{
		$this->_api_auth = TRUE;
	}

	/**
	 * Auto authorize all endpoints in the public class
	 *
	 * @return boolean
	 */
	public function actionAuthentication($action)
	{
		return$this->_action_auth = TRUE;
	}
}