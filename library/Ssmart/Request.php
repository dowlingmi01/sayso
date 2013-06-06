<?php
/**
 * <p>Sets up the default structure and default values for the
 *  request object for passing around the api
 *  these values can be overridden in the API call.</p>
 *
 * @package Ssmart
 */
class Ssmart_Request
{
	/**
	 * The class that houses the endpoint
	 *
	 * @var string
	 */
	public $action_class;

	/**
	 * The endpoint (method) of $action_class
	 *
	 * @var string
	 */
	public $action;

	/**
	 * The user id
	 *
	 * @var int
	 */
	public $api_user;

	/**
	 * The user key
	 *
	 * @var string
	 */
	public $api_key;

	/**
	 * For single requests, we are going to format it as a multi request
	 * but we need to leave some nodes at the top level.
	 * This array defines the nodes to be left alone in such case.
	 * Everything else can be assumed to be a request parameter
	 *
	 * @var array
	 */
	private $_top_level_params = array(
				"api_user",
				"api_key",
				"response_format",
				"continue_on_error",
				"user_type"

			);

	/**
	 * Sets defaults for optional parameters
	 * so they can be omited in requests:
	 *	<b>request -</b>
	 *		This is for default optional parameters that each
	 *		requests should have set.
	 *	<b>api - </b>
	 *		This is for default optional parameters that all
	 *		api calls should have set.
	 *
	 * @var array
	 */
	private $_default_parameters = array(
							"request"		=> array(
											"page_number"		=> 1,
											"results_per_page"	=> 0
										),
							"api"			=> array(
											"continue_on_error"	=> TRUE,
											"user_type"		=> "panelist",
											"response_format"	=> "json"
										)
	);

////////////////////////////////////////////////////////////////////////

	/**
	 * Constructs the request object.
	 *
	 * <p>If <code>$data</code> is passed,
	 *  populate the Ssmart_Request object.</p>
	 *
	 * @param string $data (json format)
	 */
	public function __construct($data = NULL, $error = NULL) {

		if ($data)
			$this->_processRequest($data, $error);
	}

	/**
	 * Takes the json request and converts it to a php object.
	 *
	 *<p>This converts the request
	 * to a format the php scripts can more easily manage and writes
	 * it to the Request object.</p>
	 *
	 * @param string|array $request_json
	 */
	private function _processRequest ($request, $error)
	{
		if (is_string($request))
			$requestParams = json_decode($request);
		elseif (is_array($request))
			$requestParams = $this->_convertArrayToObject($request);

		if ($requestParams)
		{
			//change param names if needed
			$validatedRequestParams = $this->_convertParamNames($requestParams);

			//proessing required for requests with params
			if (isset($validatedRequestParams->action) || isset($validatedRequestParams->requests)) //$data was passed to the construtor and has requests in it.
			{
				//apply structure to the request object as needed
				if (!isset($validatedRequestParams->requests)) //single request - needs to be formatted
				{
					foreach ($validatedRequestParams as $key => $value)
					{
						if (!in_array($key, $this->_top_level_params)) //write request level nodes to the $this->request->requests->default node
						{
							if (!isset($this->requests->default) || !$this->requests->default instanceof Ssmart_EndpointRequest)
							{
								$this->requests->default = new Ssmart_EndpointRequest();
							}
							$this->requests->default->submittedParameters->$key = $value;
						} else { //write top level nodes to the $this->request node
							$this->$key = $value;
						}
					}
				} else { //straight move, it's already formatted
					foreach ($validatedRequestParams as $key=>$value)
					{
						if ($key == "requests")
						{
							foreach ($value as $name => $content) {
								$this->requests->$name =  new Ssmart_EndpointRequest();
								$this->requests->$name->submittedParameters = $content;
							}
						} else {
							$this->$key = $value;
						}
					}
				}

				//now that they are set up properly, iterate through each request and add defaults if needed.
				//TODO: find a better way to get the object key name
				foreach ($this->requests as $requestParam => $requestParamValue)
				{
					$this->_applyDefaultRequestParameters($requestParam);
				}

			} else { //$data was passed to the constructor but only has login info. Processing for api instantiation only.
				foreach ($requestParams as $key=>$value)
				{
					$this->$key = $value;
				}
			}
		} else {
			$error->newError("invalid_request");
		}

		$this->_applyDefaultApiParameters();
	}

	/**
	 * Recursive function to convert arrays to stdClass objects
	 *
	 * @param array $array
	 * @return \stdClass
	 */
	private function _convertArrayToObject($array)
	{
		$obj= new stdClass();
		foreach ($array as $k=> $v)
		{
			//TODO: may need some more thought for deeper arrays
			//	array->object->array won't be converted
			if (is_array($v))
			{
				$v = $this->_convertArrayToObject($v);
			}

			$obj->{strtolower($k)} = $v;
		}
		return $obj;
	}

	/**
	 * Converts naming conventions
	 *
	 * Some naming conventions don't make sense both in and
	 * out of the api. This converts them so that aip usage can be
	 * semantically accurate.
	 *
	 * @param type $params
	 * @return \stdClass
	 */
	private function _convertParamNames($params)
	{
		$obj = new stdClass();
		foreach ($params as $key => $value)
		{
			if (is_array($value) || is_object($value))
			{
				$value = $this->_convertParamNames ($value);
				$obj->{$key} = $value;
			} else {
				switch ($key) {
					case "user_id" :
						$obj->api_user = $value;
						break;
					case "user_key" :
						$obj->api_key = $value;
						break;
					case "class" :
						$obj->action_class = $value;
						break;
					//TODO: find a better way to name these fields - now with authenticating the session id and key, having a field named api_user doesn't make sense here
					case "session_id" :
						$obj->api_user = $value;
						break;
					case "session_key" :
						$obj->api_key = $value;
						break;
					default :
						$obj->{$key} = $value;
				}
			}
		}

		return $obj;
	}

	/**
	 * Apply default request values to optional parameters.
	 *
	 * <p>Applies default values to parameters that are not sent in
	 * because they are optional to send in
	 * but are required by the internal api processing as defined in
	 *  <code>$this->_default_parameters["request"]</code></p>
	 *
	 * @param string $requestName
	 */
	protected function _applyDefaultRequestParameters($requestName)
	{
		foreach ($this->_default_parameters["request"] as $key => $value)
		{
			if (!isset($this->requests->$requestName->submittedParameters->$key))
			{
				$this->requests->$requestName->submittedParameters->$key = $value;
			}
		}
	}

	/**
	 *Apply default values to api parameters.
	 *
	 *<p>Applies default values to parameters that are not sent in
	 * because they are optional to send in
	 * but are required by the internal api processing as defined in
	 * <code>$this->_default_parameters["api"]</code></p>
	 *
	 */
	protected function _applyDefaultApiParameters()
	{
		foreach ($this->_default_parameters["api"] as $key => $value)
		{
			if (!isset($this->$key))
			{
				$this->$key = $value;
			}
		}
	}
}