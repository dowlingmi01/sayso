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

	/**defines and sets the default value for the requester type
	 * this is to distinguish between 'admin' users (CMS) and
	 * program calls (usage in the bar code)
	 *
	 * accepted values:
	 *	admin - default
	 *	program
	 *
	 * @var string
	 */
	public $requester_type = "admin";

	/**sets format of the api response
	 *
	 * accepted values
	 *	json - default
	 *	array
	 *	php
	 *
	 * @var string
	 */
	public $response_format = "json";

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
				"admin_api_user",
				"admin_api_key",
				"response_format"
			);

	/**sets defaults for optional request level parameters
	 * so they can be omited in requests
	 *
	 * @var array
	 */
	private $_default_parameters = array(
				"page_number"		=> 1,
				"results_per_page"	=> 0
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
	protected function _processRequest ($requestJson)
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
				$this->_applyDefaultParameters($requestParam);
			}

		} elseif ((isset($requestParams->api_key) && isset($requestParams->api_user)) || (isset($requestParams->admin_api_key) && isset($requestParams->admin_api_user))) { //processing for api instantiation only
			foreach ($requestParams as $key=>$value)
			{
				$this->$key = $value;
			}
		}
	}

	protected function _applyDefaultParameters($requestName)
	{
		foreach ($this->_default_parameters as $key => $value)
		{
			if (!isset($this->requests->$requestName->$key))
			{
				$this->requests->$requestName->$key = $value;
			}
		}
	}
}