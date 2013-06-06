<?php
/**
 * <p>Registration endpoiints</p>
 *
 * @package Ssmart
 * @subpackage endpoint
 */
class Ssmart_RegistrationEndpoint extends Ssmart_GlobalController
{
	/**
	 * Creates a new user
	 *
	 * @param Ssmart_EndpointRequest $request
	 * @return \Ssmart_EndpointResponse
	 * @throws Exception
	 */
	public function createUser(Ssmart_EndpointRequest $request)
	{
		$validators = array(
				"originating_starbar_id"	=> "int_required_notEmpty",
				"password"				=> "required",
				"email"				=> "email"
			);
		$filters = array();

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		$starbar = $request->validParameters["originating_starbar_id"];
		$pw = $request->validParameters["password"];
		$userEmail = $request->validParameters["email"];

		$email = new User_Email();
		$email->email = $userEmail;

		// create user object with filtered data
		// (before resetting the validator)
		$user = new User();
		$user->setPlainTextPassword($pw);
		$user->originating_starbar_id = $starbar;

		$user->setEmail($email);

		// save
		$user->save();

		if (!$user->id)
			throw new Exception('Failed to save user.');

		$response->setResultVariable("user_id", $user->id);

		// success
		return $response;
	}

}