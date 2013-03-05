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
	private $module_dir = "api3";

	/**sets the controller class prefix for this module
	 *
	 * @var string
	 */
	private $module_name = "Api3_";

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
		$this->auth = new Api3_Authentication();
		//authenticate api access
		$this->auth->authenticate($this->request, $this->error);

		//TODO: not sure this is the place for this
		if (isset($this->request->api_user))
		{
			$this->request->requester_type = "program";
		} elseif (isset($this->request->admin_api_user)) {
			$this->request->requester_type = "admin";
		} else {
			$this->error->newError("missing_user_credentials");
		}
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
	 * @param string $apiId
	 * @param string $apiKey
	 * @param string $request_json - json fomat
	 * @return Api3_Api
	 */
	public static function getInstance ($apiId = NULL, $apiKey = NULL, $request_json = NULL)
	{
		//prepare the request params for consistent handling
		if ($request_json)
		{
			$request = $request_json;
		} elseif ($apiId && $apiKey) {
			$request = json_encode(array('api_user'=>$apiId, 'api_key'=>$apiKey));
		} else {
			self::$_instance->error->newError("missing_params_api_instance");
		}

		//return the instance
		if (!self::$_instance)
		{
			self::$_instance = new self($request);
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
		//TODO: check for set up errors before processing

		//check api auth
		if ($this->auth->getAuthStatus() === TRUE)
		{
			//make sure our request object is strucutured properly
			if (isset($this->request->requests))
			{
				//iterate through each request for individual processing and error handling
				foreach ($this->request->requests as $key=>$value)
				{
					//authenticate action access
					$this->auth->actionAuthentication($value->action);
					if ($this->auth->getAuthStatus(TRUE) === TRUE) //action auth passed
					{
						//send to the proper model for processing
						$logicResponse = $this->_callAction($value, $key);

						//check response from model for an error code
						if (isset($logicResponse))
						{
							//deal with logic response
							$this->response->responses->$key = $logicResponse;
						} elseif ($this->error->checkForErrors() === TRUE) {
							//do nothing
						}else { //catch all for unknown errors
							$this->error->newError("error_unknown", $key);
							//TODO: log for research
						}
					} else { //action auth failed
						$this->error->newError("auth_failed_action", $key);
					}
				}
			} else { //request is not properly structured
				$this->error->newError("missing_params_request");
			}
		} else { //api auth failed
			//prepare error on api authentication
			$this->error->newError("auth_failed_api");
		}

		//process errors before returning anything.
		if ($this->error->checkForErrors() === TRUE)
		{
			//do something
			$this->error->processErrors($this->response);
		}
		//formats the response object for output
		$formattedResponse = $this->_formatResponse();
		$this->_resetObject();

		//outputs the response as it was requested
		return $formattedResponse;
	}

	/**calls the requested action
	 *
	 * @param Api3_Request $requestObject
	 * @return stdClas | void
	 */
	private function _callAction($requestObject, $requestName) {
		//prepare the class name for loading
		$actionClass = $this->module_name . ucfirst($requestObject->action_class) . "Controller";
		//prepare the file name for loading
		$classFileName = ucfirst($requestObject->action_class) . "Controller";
		//assign action name to a variable for dynamic loading
		$actionName = $requestObject->action;

		//load the class file so the method can be called
		//auto load won't work in Zend because we are calling a controller
		$fileToLoad = APPLICATION_PATH . '/modules/' . $this->module_dir . '/controllers/' . $classFileName . '.php';
		if (file_exists($fileToLoad))
		{
			include_once APPLICATION_PATH . '/modules/' . $this->module_dir . '/controllers/' . $classFileName . '.php';

			//make sure the method exists and run its logic
			if (method_exists($actionClass, $actionName))
			{
				$logicResultClass = new $actionClass();
				$logicResult = $logicResultClass->$actionName($requestObject);

				//catch errors or return logic
				if (!$logicResult instanceof Api3_ApiError)
				{
					return $logicResult;
				} else {
					$this->error->newError($logicResult->getError(), $requestName);
				}
			} else { //method does not exist
				$this->error->newError("invalid_action", $requestName);
			}
		} else { //class does not exist
			$this->error->newError("invalid_action_class", $requestName);
		}

	}

	/**Format the data that the API has prepared to match the requested
	 *  output format
	 *
	 *
	 * @return mixed - json | array | Api3_Response
	 */
	protected function _formatResponse()
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

	/**
	 * reset the request and response objects keeping the auth
	 *  object for reusability without having reauthenticate
	 */
	private function _resetObject()
	{
		$this->error = new Api3_ApiError();
		$this->request = new Api3_Request();
		$this->response = new Api3_Response();
		$this->auth->resetActionAuth();
	}

	/**
	 *
	 * @param array $params
	 */
	public function setRequest($params)
	{
		//TODO: check for errors first
		$this->request = new Api3_Request(json_encode($params), $this->error);
	}
}