<?php
/**
 * the Api object encapsulates the request, response, error
 * and auth objects for easier passing around within the code.
 */
class Api3_Api
{
	/**
	 *
	 * @var Api3_Api
	 */
	private static $_instance;

	/** sets the directory of the module
	 *
	 * @var string
	 */
	private $_module_dir = "api3";

	/**sets the controller class prefix for this module
	 *
	 * @var string
	 */
	public $module_name = "Api3_";

	/**the error object
	 *
	 * @var Api3_Error
	 */
	private $error;

	/**the request object
	 *
	 * @var Api3_Request
	 */
	private $request;

	/**the response object
	 *
	 * @var Api3_Response
	 */
	private $response;

	/**the auth object
	 *
	 * @var Api3_Autentication
	 */
	private $auth;

////////////////////////////////////////////////////

	/**Private constructor
	 * sets the error object
	 * sets the request object
	 * sets the response object
	 * sets the auth object
	 *
	 * @param string $request - json format
	 * TODO: accept more than json
	 */
	private function __construct($request)
	{
		//initialize ApiError object
		$this->error = new Api3_ApiError();

		//initialize the Request object
		$this->request = new Api3_Request($request, $this->error);

		//initialize Response object
		$this->response = new Api3_Response($this->request, $this->error);

		//initialize Auth object
		$this->auth = Api3_Authentication::getAuthentication();
		//authenticate api access
		$this->auth->apiAuthentication($this->request, $this->error);

	}

	/**returns an instance of Api3_Api with response, request,
	 *	and auth arrtibutes populated
	 *
	 * NOTE: input parameters first looks for $request_json
	 *	then checks the $apiId, $apiKey pair
	 *This is to handle the different entry points of the API
	 *	$request_json has all the required parameters
	 *	$apiId, $apiKey pair is for accessing the API directly from
	 *	code only the user id and user key are required.
	 *
	 * @param string $api_id
	 * @param string $api_key
	 * @param string $api_user_type
	 * @param string $request_json - json fomat
	 * @return Api3_Api
	 */
	public static function getInstance ($api_id = NULL, $api_key = NULL, $api_user_type = NULL, $request_json = NULL)
	{
		//prepare the request params for consistent handling
		if ($request_json) //external api request
		{
			$request = $request_json;
		} elseif ($api_id && $api_key) { //internal program request
			$userCredentials = array('api_user'=>$api_id, 'api_key'=>$api_key);
			isset($api_user_type) ? array_push($userCredentials, array("user_type" => $api_user_type)) : "";
			$request = json_encode($userCredentials);
		} else {
			//in case Api3_Error is not yet loaded - set it here and apply error after $_instance has been checked
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
			self::$_instance->error->newError("missing_params_api_instance");
		}

		return self::$_instance;
	}

	/**process all api calls
	 *
	 * checks the Api->auth object for api access
	 * checks for single or multi requests
	 * authorizes action access
	 * processes the requested logic
	 * prepares logic response into the Api->response object
	 *
	 * @return mixed string | array | stdClass
	 *	depends on the response_format requested
	 */
	public function getResponse()
	{
		$this->_processResponse();

		//process errors before returning anything.
		if ($this->error->checkForErrors() === TRUE)
		{
			$this->error->processErrors($this->response, $this->request);
		}
		//formats the response object for output
		$formattedResponse = $this->_formatResponse();
		$this->_resetObject();

		//outputs the response as it was requested
		return $formattedResponse;
	}

	/**processes the response and filter off some potential errors
	 *
	 * @return void
	 */
	private function _processResponse()
	{
		//check for set up errors before processing
		if ($this->error->checkForErrors() === TRUE)
			return;

		//check api auth
		if ($this->auth->getAuthStatus() !== TRUE)
			return $this->error->newError("auth_failed_api");

		//make sure our request object is strucutured properly
		if (!isset($this->request->requests))
			return $this->error->newError("missing_params_request");

		//iterate through each request for individual processing and error handling
		foreach ($this->request->requests as $key=>$value)
		{
			//authenticate action access
			$this->auth->actionAuthentication($value->action);
			if ($this->auth->getAuthStatus(TRUE) !== TRUE)
				return $this->error->newError("auth_failed_action", $key);

			//send to the proper model for processing
			$logicResponse = $this->_callAction($value, $key);

			//check response from _callAction for an errors or process logic
			//TODO clean this up
			if (isset($logicResponse) && $logicResponse instanceof stdClass && !isset($logicResponse->error))
			{
				//deal with logic response
				$this->response->responses->$key = $logicResponse;
			} elseif (isset($logicResponse) && $logicResponse instanceof stdClass && isset($logicResponse->error)) { //accept custom errors from the endpoints

				//prepare the custom error
				$errorName = $logicResponse->error_name;
				$errorStructure = new stdClass();
				foreach ($logicResponse->errors as $errorKey => $errorValue)
				{
					if ($errorValue instanceof stdClass)
					{
						//loop again
						foreach($errorValue as $param => $result)
						{
							$errorStructure->$errorKey->$param = $result;
						}
					} else {
						$errorStructure->$errorKey = $errorValue;
					}
				}
				//send it in
				$this->error->newError($errorName, $key, $errorStructure);

			} elseif (isset($logicResponse) && is_string($logicResponse)) { //handle errors thrown by the api
				//deal with error
				$this->error->newError($logicResponse, $key);
			} else { //catch all for unknown errors
				$this->error->newError("error_unknown", $key);
				//TODO: log for research
			}
		}
	}

	/**calls the requested action
	 *
	 * @param Api3_Request $request_object
	 * @param string $request_name
	 * @return stdClass | void
	 */
	private function _callAction($request_object, $request_name) {
		//prepare the class name for loading
		$actionClass = $this->module_name . ucfirst($request_object->action_class) . "Controller";
		//prepare the file name for loading
		$classFileName = ucfirst($request_object->action_class) . "Controller";
		//assign action name to a variable for dynamic loading
		$actionName = $request_object->action;

		//load the class file so the method can be called
		//auto load won't work in Zend because we are calling a controller
		$fileToLoad = APPLICATION_PATH . '/modules/' . $this->_module_dir . '/controllers/' . $classFileName . '.php';

		if (!file_exists($fileToLoad))
			return "invalid_action_class";

		include_once APPLICATION_PATH . '/modules/' . $this->_module_dir . '/controllers/' . $classFileName . '.php';

		//make sure the method exists and run its logic
		if (!method_exists($actionClass, $actionName))
			return "invalid_action";

		//set up session data all endpoints may use
		$sessionData = new Zend_Session_Namespace('endpoint');
		$sessionData->request_name = $request_name;
		$sessionData->auth = $this->auth;

		$logicResultClass = new $actionClass();
		$logicResult = $logicResultClass->$actionName($request_object);

		//catch errors or return logic
		return $logicResult;
	}

	/**Format the data that the API has prepared to match the requested
	 *  output format
	 *
	 *
	 * @return mixed - json | array | Api3_Response
	 */
	private function _formatResponse()
	{
		//allows for easily adding future response types eg json-d
		switch ($this->request->response_format)
		{
			case "json":
				$formattedObject = json_encode($this->response);
				break;
			case "array":
				$formattedObject = (array)$this->response;
				break;
			default:
				$formattedObject = $this->response;
		}
		return $formattedObject;

	}

	/**reset the
	 *	request
	 *	response  and
	 *	error objects
	 * as well as the per request session data
	 * while keeping the auth object for reusability without having
	 *	to re-authenticate
	 */
	private function _resetObject()
	{
		$this->error = new Api3_ApiError();
		$this->request = new Api3_Request();
		$this->response = new Api3_Response();
		$this->auth->resetActionAuth();
		//reset per request session data
		$session = new Zend_Session_Namespace("endpoint");
		foreach ($session as $key=> $value) {
			unset($session->$key);
		}
	}

	/**sets the parameters to the request object
	 *
	 * @param array $params
	 */
	public function setRequest($params)
	{
		//TODO: check for errors first
		$this->request = new Api3_Request(json_encode($params), $this->error);
	}

	/**accessor to user_type property
	 *
	 * @return string
	 */
	public function getUserType()
	{
		return $this->request->user_type;
	}
}