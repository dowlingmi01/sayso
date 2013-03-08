<?php
/**
 * sets up the default structure and default values for the
 * request object for passing around the api
 * these values can be overridden in the API call
 *
 */
class Api3_Request
{
	/**defines the class that houses the endpoint
	 *
	 * @var string
	 */
	public $action_class;

	/**defines the endpoint (method) of $action_class
	 *
	 * @var string
	 */
	public $action;

	/**the user id
	 *
	 * @var int
	 */
	public $api_user;

	/** the user key
	 *
	 * @var string
	 */
	public $api_key;

	/**for single requests, we are going to format it as a multi request
	 * but we need to leave some nodes at the top level
	 * this array defines the nodes to be left alone in such case
	 * everything else can be assumed to be a request parameter
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

	/**sets defaults for optional parameters
	 * so they can be omited in requests
	 *	request
	 *		this is for default optional parameters that each
	 *		requests should have set
	 *	api
	 *		this is for default optional parameters that all
	 *		api calls should have set
	 *
	 * @var array
	 */
	private $_default_parameters = array(
							"request"	=> array(
								"page_number"		=> 1,
								"results_per_page"	=> 0
							),
							"api"		=> array(
								"continue_on_error"	=> TRUE,
								"user_type"		=> "user",
								"response_format"	=> "json"
							)
	);

////////////////////////////////////////////////////////////////////////

	/**constructs the request object
	 *	checks if a json string has been passed
	 *	then sets the requester_type based on
	 *		the user id variable passed
	 *
	 * @param string $data - json format
	 */
	public function __construct($data = NULL, Api3_ApiError $error = NULL) {

		if ($data)
		{
			$this->_processRequest($data);
		}
	}

	/**Takes the json request and converts it to a php object
	 *
	 * All requests must be in json format until extended. This converts the request
	 * to a format the php scripts can more easily manage and writes
	 * it to the Request object.
	 *
	 * @param string $request_json
	 */
	private function _processRequest ($requestJson)
	{
		$requestParams = json_decode($requestJson);
		//proessing required for requests with params
		if (isset($requestParams->action) || isset($requestParams->requests))
		{
			//apply structure to the request object as needed
			if (!isset($requestParams->requests)) //single request - needs to be formatted
			{
				foreach ($requestParams as $key => $value)
				{
					if (!in_array($key, $this->_top_level_params)) //write request level nodes to the $this->request->requests->default node
					{
						$this->requests->default->$key = $value;
					} else { //write top level nodes to the $this->request node
						$this->$key = $value;
					}
				}
			} else { //straight move, it's already formatted
				foreach ($requestParams as $key=>$value)
				{
					$this->$key = $value;
				}
			}

			//now that they are set up properly, iterate through each request and add defaults if needed.
			foreach ($this->requests as $requestParam => $requestParamValue)
			{
				$this->_applyDefaultRequestParameters($requestParam);
			}

		} elseif (isset($requestParams->api_key) && isset($requestParams->api_user)) { //processing for api instantiation only
			foreach ($requestParams as $key=>$value)
			{
				$this->$key = $value;
			}
		} else {
			$this->error->newError("missing_user_credentials");
		}
		$this->_applyDefaultApiParameters();
	}

	/**applies default values to parameters that are not sent in
	 *  because they are optional to send in
	 * but are required by the internal api processing as defined in
	 * $this->_default_parameters["request"]
	 *
	 * @param string $requestName
	 */
	protected function _applyDefaultRequestParameters($requestName)
	{
		foreach ($this->_default_parameters["request"] as $key => $value)
		{
			if (!isset($this->requests->$requestName->$key))
			{
				$this->requests->$requestName->$key = $value;
			}
		}
	}

	/**applies default values to parameters that are not sent in
	 *  because they are optional to send in
	 *but are required by the internal api processing as defined in
	 * $this->_default_parameters["api"]
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