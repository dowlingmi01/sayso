<?php
/**
 * <p>The Api error object</p>
 *
 * @package Ssmart
 */
class Ssmart_ApiError
{
	/**
	 * List of api level errors
	 *
	 *<p>Type dictates how the error is processed.</p>
	 *<p>"api" type will not return any results.</p>
	 *<p>"action" errors only affect the action called.</p>
	 *<p>Other called actions may still return results.</p>
	 *
	 * @var array
	 */
	private $_error_codes = array(
		"error_unknown"					=> array("type" => "api",		"message" => "Unknown error occured"),

		"auth_failed_api"				=> array("type" => "api",		"message" => "API authentication failed"),
		"auth_failed_action"				=> array("type" => "action",	"message" => "Action authentication failed"),
		"auth_load_fail"					=> array("type" => "api",		"message" => "Authentication error. Invalid user type submitted."),

		"invalid_action_class"				=> array("type" => "action",	"message" => "Invalid api class"),
		"invalid_action"					=> array("type" => "action",	"message" => "Invalid action"),
		"invalid_data_type"				=> array("type" => "action",	"message" => "Data type validation errror. Please check the submitted data types"),
		"invalid_request"				=> array("type" => "api",		"message" => "The request data is invalid"),

		"missing_params_api_instance"		=> array("type" => "api",		"message" => "Api user & key or json request are required"),
		"missing_params_request"			=> array("type" => "api",		"message" => "Required parameters missing"),
		"missing_params_panelist_auth"		=> array("type" => "api",		"message" => "Missing api_user or api_key parameters"),

		"endpoint_parameter_validation_failed"=> array("type" => "action",	"message" => "Endpoint data type validation failed."),
		"endpoint_failed"				=> array("type" => "action",	"message" => "Endpoint parameter filter and validation failed."),

		"common_data_type_not_found"		=> array("type" => "action",	"message" => "No common data of that type found."),

		"continue_on_errors"				=> array("type" => "api",		"message" => "A sibling request encountered an error with continue_on_errors set to FALSE. No response provided."),
	);

	/**
	 * Holds the errors as they are porcessed.
	 *
	 * @var array
	 */
	private $_errors = array();

////////////////////////////////////////

	/**
	 * Sets an error in the Ssmart_Error object.
	 *
	 * @param string $error			The name of the error
	 * @param string $responseName	The name of the response node the error gets applied to
	 * @param bool|mixed $custom_error	The custom error message that can be passed
	 */
	public function newError($error, $responseName = "default", $custom_error = FALSE)
	{
		if (array_key_exists($error, $this->_error_codes))
		{
			$this->_errors[] = array(
							"code"			=> $error,
							"message"			=> $this->_error_codes[$error]["message"],
							"type"			=> $this->_error_codes[$error]["type"],
							"response_name"	=> $responseName
						);
		} elseif ($custom_error) {
			//inject a custom error e.g. failed param validation
			//message param can accept a string, array, or object. it will be converted to json later
			$this->_errors[] = array(
							"code"			=> $error,
							"message"			=> $custom_error,
							"type"			=> "action",
							"response_name"	=> $responseName
						);
		} else { //this is a debug to catch all incorrect errors
			$this->_errors[] = array(
							"code"			=> "invalid_error",
							"message"			=> "There was an error throwing the requested error. The error you passed '{$error}', is not defined.",
							"type"			=> "api"
						);
		}
	}

	/**
	 * Whether there are any errors in the current instantiation of the Error object
	 *
	 * @return boolean
	 */
	public function checkForErrors()
	{
		if (count($this->_errors) > 0)
			return TRUE;
		else
			return FALSE;
	}

	/**
	 * Inserts or replaces response with the appropriate error message.
	 *
	 * <p>If an error of type api is found, it will break and return that alone.
	 *  This allows for the proper ordering of errors.</p>
	 *<p>For example, if an invalid_class error is thrown, it's logical that a subsequent
	 * invalid_action error would occur even if the action does exist.
	 * a return of invalid_action does not help a developer fix their code if thrown out of order.</p>
	 *
	 * @param Ssmart_Response $response
	 * @param Ssmart_Request $request
	 */
	public function processErrors($response, $request)
	{
		$count = count($this->_errors);
		//check for the continue_on_erors setting
		if(property_exists($request, "continue_on_error") && $request->continue_on_error === FALSE)
		{
			foreach($response->responses as $key => $value)
			{
				if ($this->_errors[0]["response_name"] != $key)
				{
					$response->responses->$key = "";

					$response->responses->$key->errors_returned = $count;
					$response->responses->$key->errors->continue_on_errors = $this->_error_codes["continue_on_errors"]["message"];
				}
			}
		}
		//process the errors
		foreach ($this->_errors as $key => $value)
		{
			//if type=api stop processing errors
			//api types do not allow return values
			if ($value["type"] == 'api' || $value["type"] == "other")
			{
				if (isset($response->responses))
				{
					unset($response->responses);
				}
				$response->error_code = $value["code"];
				$response->error_message = $value["message"];
				break;
			} else { //action type errors are returned for each request
				//remove the response if one got through
				if (isset($response->responses->$value["response_name"]->records))
				{
					unset($response->responses->$value["response_name"]->records);
				}

				//set the error
				$response->responses->$value["response_name"]->errors_returned = $count;
				$response->responses->$value["response_name"]->errors->$value["code"] = $value["message"];
			}
		}
	}
}