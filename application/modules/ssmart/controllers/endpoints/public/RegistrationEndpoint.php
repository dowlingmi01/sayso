<?php
/**
 * <p>Registration endpoiints</p>
 *
 * @package Ssmart
 * @subpackage endpoint
 */
class Ssmart_Public_RegistrationEndpoint extends Ssmart_GlobalController
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
		$starbarId = $request->valid_parameters["originating_starbar_id"];
		$pw = $request->valid_parameters["password"];
		$userEmail = strtolower($request->valid_parameters["email"]);

		$userId = User::create($userEmail, $pw, $starbarId);

		$response->setResultVariable("user_id", $userId);

		// success
		return $response;
	}
	public function createMachinimaReloadUser(Ssmart_EndpointRequest $request)
	{
		$validators = array(
			"digest"			=> "required",
			"email"				=> "email"
		);
		$filters = array();

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		$starbarId = 7;
		$digest = $request->valid_parameters["digest"];
		$email = strtolower($request->valid_parameters["email"]);

		$userId = User::create($email, '', $starbarId, array('digest'=>$digest));

		$response->setResultVariable("user_id", $userId);

		// success
		return $response;
	}
}
