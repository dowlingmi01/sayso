<?php
	/**
	 * <p>Contact endpoiint</p>
	 *
	 * @package Ssmart
	 * @subpackage endpoint
	 */

class Ssmart_Public_ContactEndpoint {
	/**
	 * Handles sending a contact request
	 *
	 * @param Ssmart_EndpointRequest $request
	 * @return \Ssmart_EndpointResponse
	 * @throws Exception
	 */
	public function send(Ssmart_EndpointRequest $request)
	{
		$validators = array(
			"from_address"			=> "email",
			"subject"				=> "required",
			"message"				=> "required",
			"message_meta"			=> "required_allowEmpty",
			"starbar_id"			=> "int_required_notEmpty"
		);
		$filters = array();

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		$starbar_id = $request->valid_parameters["starbar_id"];
		$starbar = new Starbar();
		$starbar->loadData($starbar_id);
		$starbarShortName = "[" . ucfirst($starbar->short_name) . "] ";
		$toAddress = "contact@say.so";
		$fromAddress = "contact@say.so";
		$subject = $starbarShortName . $request->valid_parameters["subject"];
		$message = $request->valid_parameters["message"];
		$messageMeta = $request->valid_parameters["message_meta"] == "" ? NULL : $request->valid_parameters["message_meta"];

		try {
			$mail = new Email();
			$sent = $mail->send($toAddress, $fromAddress, $message, $subject, $messageMeta);
		} catch (Exception $e) {
			throw new Exception("Message failed to send. " . $e->getMessage());
		}

		if ($sent === TRUE)
			$response->setResultVariable("success", TRUE);
		else
			$response->setResultVariable("success", FALSE);
		return $response;
	}

}