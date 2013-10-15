<?php
/**
 * <p>The Api object encapsulates the request, response, error
 * and auth objects for easier passing around within the code.</p>
 *
 * @package Ssmart
 */
class Ssmart_Api
{
	const ENDPOINT_DIRECTORY_PANELIST = "panelist";
	const ENDPOINT_DIRECTORY_PUBLIC = "public";

	/**
	 * The api instance.
	 *
	 * @var Ssmart_Api
	 */
	private static $_instance;

	/**
	 * Sets the directory of the module.
	 *
	 * @var string
	 */
	private $_module_dir = "ssmart";

	/**
	 * Sets the controller class prefix for this module.
	 *
	 * @var string
	 */
	private $_module_name = "Ssmart";

	/**
	 * The error object.
	 *
	 * @var Ssmart_ApiError
	 */
	private $_error;

	/**
	 * The request object.
	 *
	 * @var Ssmart_Request
	 */
	private $_request;

	/**
	 * The response object.
	 *
	 * @var Ssmart_Response
	 */
	private $_response;

	/**
	 * The auth object.
	 *
	 * @var Ssmart_Authentication
	 */
	private $_auth;

////////////////////////////////////////////////////
	/**
	 * Private constructor
	 *
	 * <p>Sets the error object.</p>
	 * <p>Sets the request object.</p>
	 * <p>Sets the response object.</p>
	 * <p>Sets the auth object.</p>
	 *
	 * @param string $request json format
	 */
	private function __construct($request)
	{
		//TODO: accept more than json.
		//initialize ApiError object
		$this->_error = new Ssmart_ApiError();

		//initialize the Request object
		$this->_request = new Ssmart_Request($request, $this->_error);

		//initialize Response object
		$this->_response = new Ssmart_Response($this->_request);

		//initialize Auth object
		$this->_auth = Ssmart_Authentication::getAuthentication($this->getUserType(), $this->getModuleName());
		if ($this->_auth !== FALSE)
			$this->_auth->apiAuthentication($this->_request, $this->_error);
		else
			$this->_error->newError("auth_load_fail", $this->_request);
	}

	/**
	 * Returns an instance of Ssmart_Api with response, request,
	 *	and auth attributes populated.
	 *
	 *<p>Input parameters first looks for <code>$request_json</code>
	 *	then checks the <code>$apiLoginCredentials</code> array</p>
	 *<p>This is to handle the different entry points of the API</p>
	 *<p><code>$request_json</code> has all the required parameters</p>
	 *<p><code>$apiLoginCredentials</code> is for accessing the API directly from
	 *	code. Only the user id and user key are required. The user type is optional.</p>
	 *
	 * @param array $apiLoginCredentials
	 *	<p><b>required params:</b>
	 *		user_id,
	 *		user_key</p>
	 *	<p><b>option params:</b>
	 *		user_type</p>
	 * @param string $requestJson (json format)
	 * @return Ssmart_Api
	 */
	public static function getInstance ($apiLoginCredentials = NULL, $requestJson = NULL)
	{
		//prepare the request params for consistent handling
		if ($requestJson) //external api request
		{
			$request = $requestJson;
		} elseif ($apiLoginCredentials) { //internal program request
			$validLoginCredentials = self::_processLoginCredentials($apiLoginCredentials);

			if ($validLoginCredentials === FALSE)
				$error = "missing_params_panelist_auth";
			else
				$request = json_encode($validLoginCredentials);
		} else {
			//in case Ssmart_Error is not yet loaded - set it here and apply error after $_instance has been checked
			$error = "missing_params_api_instance";
		}

		//return the instance
		if (!self::$_instance)
		{
			self::$_instance = new self($request);
		}

		//set error if triggered above
		if (isset($error))
		{
			self::$_instance->_error->newError($error, $request);
		}

		return self::$_instance;
	}

	/**
	 * Formats the user credentials as passed from the api instantiation.
	 * Returns false if required parameters are missing.
	 *
	 * @param array $apiLoginCredentials
	 * @return boolean|array
	 */
	private static function _processLoginCredentials($apiLoginCredentials)
	{
		if (!isset($apiLoginCredentials["session_id"]) || $apiLoginCredentials["session_id"] == "")
			return FALSE;
		if (!isset($apiLoginCredentials["session_key"]) || $apiLoginCredentials["session_key"] == "")
			return FALSE;
		$userCredentials = array('session_id'=>$apiLoginCredentials["session_id"], 'session_key'=>$apiLoginCredentials["session_key"]);
		isset($apiLoginCredentials["user_type"]) ? array_push($userCredentials, array("user_type" => $apiLoginCredentials["user_type"])) : "";
		return $userCredentials;
	}

	/**
	 * Processes all api calls.
	 *
	 *<p>Processes request.</p>
	 * <p>Formats response.</p>
	 * <p>Resets objects for this request/response.</p>
	 *
	 * @return mixed Depends on the response_format requested.
	 */
	public function getResponse()
	{
		//process request
		//this function sets the response object based on the request object
		$this->_processRequest();

		//process errors before returning anything.
		if ($this->_error->checkForErrors() === TRUE)
			$this->_error->processErrors($this->_response, $this->_request);

		//formats the response object for output
		$formattedResponse = $this->_formatResponse();

		//reset all one time use objects and settings
		$this->_resetObjects();

		//outputs the formatted response
		return $formattedResponse;
	}

	/**
	 * Processes the response and filter off some potential errors.
	 *
	 * <p>Checks for errors.</p>
	 * <p>Checks auth status.</p>
	 * <p>Checks to make sure a proper request exists.</p>
	 * <p>For each request:</p>
	 *<p>Check action auth.</p>
	 *<p>Send request to endpoint.</p>
	 *<p>Validate endpoint response.</p>
	 *<p>Handle errors.</p>
	 */
	private function _processRequest()
	{
		//check for set up errors before processing
		if ($this->_error->checkForErrors() === TRUE)
			return;

		//check api auth
		if (!$this->_auth || $this->_auth->getAuthStatus() !== TRUE)
			return $this->_error->newError("auth_failed_api", $this->_request);

		//make sure our request object is structured properly
		if (!isset($this->_request->requests) || !get_object_vars($this->_request->requests))
			return $this->_error->newError("missing_params_request", $this->_request);

		//iterate through each request for individual processing and error handling
		foreach ($this->_request->requests as $key=>$value)
		{
			//set request auth
			$value->auth = $this->_auth;

			//authenticate action access
			$this->_auth->actionAuthentication($value->submitted_parameters->action);
			if ($this->_auth->getAuthStatus(TRUE) !== TRUE)
				return $this->_error->newError("auth_failed_action", $value, $key);

			//send to the proper model for processing
			$logicResponse = $this->_callAction($value, $key);

			//check response from _callAction for an errors or process logic
			if (isset($logicResponse) && $logicResponse instanceof Ssmart_EndpointResponse)
			{
				//deal with logic response
				$this->_response->responses->$key = $logicResponse;

				//deal with common data
				if ($logicResponse->hasCommonData())
					$this->_processCommonData($logicResponse->getCommonData());

				//flag new session_key if necessary
				if (isset($this->_auth->user_data->new_session_key))
					$this->_processCommonData(array("new_session_key" => $this->_auth->user_data->new_session_key, "new_session_id" => $this->_auth->user_data->new_user_session_id));

				//log api call
				$logApiCall = new Ssmart_ApiLog_Request();
				$logApiCall->logApiRequest($value);
			} elseif (isset($logicResponse) && $logicResponse instanceof Exception) { //exceptions thrown by the endpoint
				//deal with error
				if ($logicResponse instanceof Ssmart_EndpointError)
					$this->_error->newError($logicResponse->meta->error_name, $value, $key, $logicResponse->errors, $logicResponse);
				else
					$this->_error->newError("endpoint_exception", $value, $key, $logicResponse->getMessage(), $logicResponse);
			} elseif (isset($logicResponse) && is_string($logicResponse)) { //handle errors thrown by the api
				//deal with error
				$this->_error->newError($logicResponse, $value, $key);
			} else { //catch all for unknown errors
				$this->_error->newError("error_unknown", $value, $key);
			}
		}
	}

	/**
	 * Calls a single endpoint
	 *
	 * <p>Based on the action_class and action params of the requestObject
	 * this function loads the requested class.</p>
	 * <p>The structure of this API is such that Zend cannot auto load endpoints
	 * so we have to manually load them.</p>
	 *
	 * @param Ssmart_Request $requestObject Formatted request parameters.
	 * @param string $requestName The name of the request.
	 * @return stdClass The return logic may contain an error or the endpoint response.
	 */
	private function _callAction($requestObject, $requestName) {
		//prepare the class name for loading
		$actionClass = $this->_module_name . "_" . ucfirst($this->_auth->user_type) . "_" . ucfirst($requestObject->submitted_parameters->action_class) . "Endpoint";
		//prepare the file name for loading
		$classFileName = ucfirst($requestObject->submitted_parameters->action_class) . "Endpoint";
		//assign action name to a variable for dynamic loading
		$actionName = $requestObject->submitted_parameters->action;

		//load the class file so the method can be called
		//auto load won't work in Zend because we are calling a controller
		$fileToLoad = APPLICATION_PATH . '/modules/' . $this->_module_dir . '/controllers/endpoints/' . $this->_request->user_type . "/" . $classFileName . '.php';

		if (!file_exists($fileToLoad))
		{
			//check to see if it is a public ep called as a panelist ep and load it anyway.
			if ($this->_request->user_type == self::ENDPOINT_DIRECTORY_PANELIST)
			{
				$fileToLoad = str_replace(self::ENDPOINT_DIRECTORY_PANELIST, self::ENDPOINT_DIRECTORY_PUBLIC, $fileToLoad);
				if (file_exists($fileToLoad))
				{
					$actionClass = str_replace(ucfirst(self::ENDPOINT_DIRECTORY_PANELIST), ucfirst(self::ENDPOINT_DIRECTORY_PUBLIC), $actionClass);
				} else {
					return "invalid_action_class";
				}
			} else
				return "invalid_action_class";
		}

		include_once $fileToLoad;

		//make sure the method exists and run its logic
		if (!method_exists($actionClass, $actionName))
			return "invalid_action";

		//load the endpoint
		$logicResultClass = new $actionClass($requestName, $this->_auth);
		try {
			$logicResult = $logicResultClass->$actionName($requestObject);
		} catch(Exception $e) {
			$logicResult = $e;
		}
		return $logicResult;
	}

	/**
	 * Format the data that the API has prepared to match the requested
	 *  output format.
	 *
	 * @return mixed - json|array|Ssmart_Response
	 */
	private function _formatResponse()
	{
		//allows for easily adding future response types eg json-d
		switch ($this->_request->response_format)
		{
			case "json":
				$formattedObject = json_encode($this->_response);
				break;
			case "array":
				$formattedObject = (array)$this->_response;
				break;
			default:
				$formattedObject = $this->_response;
		}
		return $formattedObject;

	}

	/**
	 *Resets objects after they have been used.
	 *
	 * <p>Reset the:<br />
	 *	request,<br />
	 *	response,<br />
	 *	and error objects<br />
	 * while keeping the auth object for reuse without having
	 * to re-authenticate</p>
	 */
	private function _resetObjects()
	{
		$this->_error = new Ssmart_ApiError();
		$this->_request = new Ssmart_Request();
		$this->_response = new Ssmart_Response();
		if ($this->_auth)
			$this->_auth->resetActionAuth();
	}

	/**
	 * Sets the parameters to the request object.
	 *
	 * @param array $params
	 */
	public function setRequest($params)
	{
		//TODO: check for errors first
		$this->_request = new Ssmart_Request(json_encode($params));
	}

	/**
	 * Accessor to user_type property.
	 *
	 * @return string
	 */
	public function getUserType()
	{
		return $this->_request->user_type;
	}

	/**
	 * Accessor to _module_name property.
	 *
	 * @return string
	 */
	public function getModuleName()
	{
		return $this->_module_name;
	}

	/**
	 * @param $commonData
	 */
	private function _processCommonData($commonData)
	{
		foreach ($commonData as $key => $value)
		{
			$this->_response->common_data[$key] = $value;
		}
	}
}