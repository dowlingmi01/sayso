<?php
/**
 * <p>Forgot password endpoiints</p>
 *
 * @package Ssmart
 * @subpackage endpoint
 */
class Ssmart_Public_ForgotPasswordEndpoint extends Ssmart_GlobalController
{
	public function createRequest(Ssmart_EndpointRequest $request)
	{
		$validators = array(
			"starbar_id"		=> "int_required_notEmpty",
			"email"				=> "email",
		);
		$filters = array();

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		$userEmail = strtolower($request->valid_parameters["email"]);
		$starbarId = $request->valid_parameters["starbar_id"];

		$email = new User_Email();

		$email->loadDataByUniqueFields(array('email'=>$userEmail));
		if (!$email->id)
			throw new Exception("EMAIL_ADDRESS_NOT_FOUND");

		$passwordRequest = new User_PasswordChangeRequest();

		if (!$passwordRequest->sendToUser($email, $starbarId))
			throw new Exception("REQUEST_FAILED");

		$response->setResultVariable("success", true);

		// success
		return $response;
	}

	public function changePassword(Ssmart_EndpointRequest $request)
	{
		$validators = array(
			"verification_code" => "required",
			"new_password" => "required",
		);
		$filters = array();

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		$verificationCode = $request->valid_parameters["verification_code"];
		$newPassword = $request->valid_parameters["new_password"];

		$passwordRequest = new User_PasswordChangeRequest();
		$passwordRequest->loadDataByUniqueFields(["verification_code" => $verificationCode, "has_been_fulfilled" => null]);

		if (!$passwordRequest->id)
			throw new Exception("REQUEST_FAILED");

		$user = new User();
		$user->loadData($passwordRequest->user_id);
		$user->setPlainTextPassword($newPassword);
		$user->save();

		$passwordRequest->has_been_fulfilled = 1;
		$passwordRequest->save();

		$response->setResultVariable("success", true);

		// success
		return $response;
	}
}