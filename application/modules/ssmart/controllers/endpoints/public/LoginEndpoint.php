<?php
/**
 * <p>Login endpoiints</p>
 *
 * @package Ssmart
 * @subpackage endpoint
 */

class Ssmart_Public_LoginEndpoint extends Ssmart_GlobalController
{
	/**
	 * Handles logging in a user and returning the session data.
	 *
	 * @param Ssmart_EndpointRequest $request
	 * @return \Ssmart_EndpointResponse
	 * @throws Exception
	 */
	public function login(Ssmart_EndpointRequest $request)
	{
		$validators = array(
			"username"			=> "email",
			"password"				=> "required"
		);
		$filters = array();

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		$username = $request->valid_parameters["username"];
		$pw = $request->valid_parameters["password"];

		$loginData = User_Login::loginWithEmail($username, $pw);
		if (!$loginData)
			throw new Exception("Login failed");

		$response->setResultVariable("session_id", $loginData["session"]->id);
		$response->setResultVariable("session_key", $loginData["session"]->session_key);

		// success
		return $response;
	}

	/**
	 * Renders the current session inactive.
	 *
	 * @param Ssmart_EndpointRequest $request
	 * @return \Ssmart_EndpointResponse
	 * @throws Exception
	 */
	public function logout(Ssmart_EndpointRequest $request)
	{
		$validators = array(
			"current_session_id"			=> "int_required_notEmpty"
		);
		$filters = array();

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		User_Session::logout($request->valid_parameters["current_session_id"]);

		$response->setResultVariable("success", TRUE);

		// success
		return $response;
	}


}