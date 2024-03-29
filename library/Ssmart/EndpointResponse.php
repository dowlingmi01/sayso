<?php
/**
 * <p>Sets up the default structure and default values for the
 * endpoint response object</p>
 * <p>Provides endpoint response related functions and accessors.</p>
 *
 * @package Ssmart
 * @subpackage endpoint
 */
class Ssmart_EndpointResponse
{
	/**
	 *Holds the request data.
	 *
	 * @var Ssmart_EndpointRequest
	 */
	private $_request;

	/**
	 *Sets a default page number for the pagination functions.
	 *
	 * @var int
	 */
	private $_default_page_number = 1;

	/**
	 * Sets the default results per page for the pagination functions.
	 *
	 * @var int
	 */
	private $_default_results_per_page = 50;

	/**
	 *Filters to be applied be to the request params.
	 *
	 * @var array
	 */
	private $_filters = array();

	/**
	 *Validators to be applied to the request params.
	 *
	 * @var array
	 */
	private $_validators = array();

	/**
	 *TODO:find out what this is for
	 *
	 * @var stdClass()
	 */
	public $variables = null;

	/**
	 * Common data to be passed to the response.
	 *
	 * @var array
	 */
	private $_common_data = array();

//////////////////////////////////////////////////////

	/**
	 * Constructor
	 *
	 * <p>Sets the request object to be available in the response object.</p>
	 *
	 * @param Ssmart_EndpointRequest $request
	 * @param Ssmart_EndpointRequest $filters
	 * @param Ssmart_EndpointRequest $validators
	 */
	public function __construct($request, $filters = NULL, $validators = NULL) {
		$this->_request = $request;
		$this->setFilters($filters);
		$this->setValidators($validators);
		$this->_validateParams();
		if ($this->hasErrors())
			throw $this->errors;

		$this->variables = new stdClass();
	}

	/**
	 * Adds the validators to the request object.
	 *
	 * @param array $validatorsArray
	 */
	public function setValidators($validatorsArray)
	{
		$this->_validators = $validatorsArray ? Ssmart_EndpointValidator::getValidators($validatorsArray) : "";
	}

	/**
	 * Adds the filters to the request object.
	 *
	 * @param array $filtersArray
	 */
	public function setFilters($filtersArray)
	{
		$this->_filters = $filtersArray ? Ssmart_EndpointValidator::getFilters($filtersArray) : "";
	}

	/**
	 * Adds contexts to the response object.
	 *
	 * <p>The value of <code>$value</code> can be a string, array, or object structure.</p>
	 * <p>It will be passed as it is to the api output.</p>
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function setResultVariable($name, $value)
	{
		@$this->variables->$name = $value;
	}

	public function setResultVariables($data)
	{
		if (!is_array($data) && !is_object($data))
			return;
		foreach ($data as $key => $value)
		{
			$this->setResultVariable($key, $value);
		}
	}

	/**
	 * Adds an error to the response object.
	 *
	 * <p>The value of <code>$customError</code> can be a string,
	 *  array, or object structure.</p>
	 * <p>It will be passed as it is structured to the api output.</p>
	 * <p>If <code>$customError</code> is an array or object, it
	 * needs a param called "code" and one called "message"</p>
	 *
	 * @param mixed $customError Can be string, array, or object.
	 * @throws Exception
	 */
	public function setResponseError($customError)
	{
		if (is_string($customError))
		{
			$this->errors = new Ssmart_EndpointError($customError);
			$this->errors->addError($customError);
		} elseif (is_array($customError)) {
			$this->errors = new Ssmart_EndpointError($customError["code"]);
			$this->errors->addError($customError["message"]);
		} elseif (is_object($customError)) {
			$this->errors = new Ssmart_EndpointError($customError->code);
			$this->errors->addError($customError->message);
		} else {
			throw new Exception("You must provide a proper error structure.");
		}
	}

	/**
	 * Add records to the response object from a
	 * prepared SQL statement.
	 *
	 * @param string $sql Valid SQL SELECT statement
	 * @param array $params Data to bind to the select statement
	 */
	public function addRecordsFromSql($sql, $params = NULL)
	{
		$sqlResponse = $this->getRecordsFromSql($sql, $params);
		if (!$sqlResponse)
			//TODO: test mysql_error() handling.
			$this->addError("SQL Error", mysql_error());
		else
			$this->addRecordsFromArray ($sqlResponse);
	}

	/**
	 * Get the records from a SQL SELECT statement.
	 *
	 * @param string $sql Valid SQL SELECT statment
	 * @param array $params Data to bind to the select statement
	 * @return array
	 */
	public function getRecordsFromSql($sql, $params = NULL)
	{
		$db = $this->_db();
		return $db->fetchAssoc($sql, $params);
	}

	/**
	 * Get a single record from a SQL SELECT statement
	 *
	 * @param string $sql Valid SQL SELECT statment
	 * @param array $params Data to bind to the select statement
	 * @return string
	 */
	public function getFieldFromSql($sql, $params = NULL)
	{
		$db = $this->_db();
		return $db->fetchOne($sql, $params);
	}

	/**
	 * Adds records to the response object from an array.
	 *
	 * @param array $array
	 */
	public function addRecordsFromArray($array)
	{
		$this->records = array();
		foreach ($array as $key => $value) {
            if (!array_key_exists("id", $value))
                $value["id"] = $key;
			$this->records[] = $value;
		}
	}

	/**
	 * Get the response of another endpoint.
	 *
	 * @param type $action
	 * @param type $class
	 * @param type $params
	 * @param type $requestName
	 * @return Ssmart_EndpointResponse
	 * @throws Exception
	 */
	public function getFromOtherEndpoint($action, $class, $params, $requestName)
	{
		//TODO: validate action authentication.
		//format $params
		$request = new Ssmart_EndpointRequest();
		$request->loadParams($params, $this->_request->auth);

		//TODO: check if class exists
		$otherClass = new $class($requestName, $this->_request->auth);
		$otherEndpointResponse = $otherClass->$action($request);
		if ($otherEndpointResponse)
			return $otherEndpointResponse;
		else
			throw new Exception("Endpoint {$action} failed.");
	}


	/**
	 * Adds the response of another endpoint to the response of this endpoint.
	 *
	 * @param string $action The name of the action in the other endpoint to call.
	 * @param string $class The name of the class that holds the other endpoint.
	 * @param array $params The params to be passed to the other endpoint.
	 * @param string $requestName The name of the api request.
	 */
	public function addFromOtherEndpoint($action, $class, $params, $requestName)
	{
		$otherEndpointResponse = $this->getFromOtherEndpoint($action, $class, $params, $requestName);

		if (isset($otherEndpointResponse->variables))
			$this->variables = $otherEndpointResponse->variables;
		if (isset($otherEndpointResponse->records))
			$this->records = $otherEndpointResponse->records;
	}

	/**
	 * Converts a Collection object into an associative array.
	 *
	 * @param Collection $collection
	 * @return array
	 */
	public function getRecordsFromCollection(Collection $collection)
	{
		$data = $collection->getArray();

		//format the result set and add the _key_idntifier value to the result set so the api can fomrat the response correctly
		$formattedData = array();
		foreach($data as $key => $value)
		{
			$formattedData[$key] = $value->getData();
		}

		return $formattedData;
	}

	/**
	 * Adds redords to the response from a Colection data type.
	 * Also adds pagination.
	 *
	 * @param Collection $collection
	 */
	public function addRecordsFromCollection(Collection $collection)
	{
		$formattedData = $this->getRecordsFromCollection($collection);

		//count logic
		$count = count($formattedData);

		$this->addRecordsFromArray($formattedData);

		if ($count > 0)
			$this->setPagination($count);
	}

	/**
	 * Check to see of this response has processed any errors yet.
	 *
	 * @return boolean
	 */
	public function hasErrors()
	{
		if (isset($this->errors))
		{
			return TRUE;
		}
	}

	/**
	 * Calls the pagination setter functions
	 *
	 * @param int $count The total records possible without pagination
	 */
	public function setPagination($count)
	{
		if (isset($this->records))
		{
			//records returned
			$this->_setRecordsReturned();
			//total records
			$this->totalRecords = (int)$count;
			//page number
			$this->_setPageNumber();
			//results per page
			$this->_setResultsPerPage();
			//total pages
			$this->_setTotalPages();
		}
	}

	/**
	 * Runs the sql to count total possible results.
	 * Then calls setPagination
	 *
	 * @param string $sql SQL formatted string
	 */
	public function setPaginationFromSql($sql, $params = NULL)
	{
		$db = Zend_Registry::get('db');
		$count = $db->fetchOne($sql, $params);
		$this->setPagination($count);
	}

	public function paginateArray($array, Ssmart_EndpointRequest $request)
	{
		if (!isset($request->valid_parameters["results_per_page"]) || !isset($request->valid_parameters["page_number"]))
			return $array;

		//set vars and data types
		$resultPerPage = (int)$request->valid_parameters["results_per_page"];
		$pageNumber = (int)$request->valid_parameters["page_number"];

		$book = array_chunk($array, $resultPerPage, TRUE);
		if (!array_key_exists($pageNumber - 1, $book))
			return array();
		$page = $book[$pageNumber - 1];

		return $page;
	}

	public function getCommonData()
	{
		return $this->_common_data;
	}

	//TODO: common data is not handled very well for extensibility purposes.
	//For one, multiple common data elements will overwrite each other.
	//For two, adding common deata elements is not easy or semantically correct.
	public function getCommonDataFromModel($dataType, $params)
	{
		switch($dataType)
		{
			case "game":
				$commonData = array("game" => Game_Transaction::getGame( $params["user_id"], $params["economy_id"] ));
				break;
			case "user":
				$userData = $this->getFromOtherEndpoint("getUser", $params["class"], array(), $params["request_name"]);
				$userDataRecord = $userData->records[0];
				$commonData = array("user" => $userDataRecord);
				break;
			default:
				throw new Ssmart_EndpointError("common_data_type_not_found");
		}

		return $commonData;
	}

	public function addCommonData($dataType, $params)
	{
		$commonData = $this->getCommonDataFromModel($dataType, $params);

		//add to the response object
		$this->_common_data = $commonData;
	}

	public function hasCommonData()
	{
		if (!empty($this->_common_data))
			return TRUE;
	}

	/**
	 * Provides a tool for getting a count of results.
	 * May not be applicable to all situations
	 *
	 * @param array|\stdClass $results
	 * @return int
	 */
	protected function _countResults($results)
	{
		$count =0;
		foreach ($results as $value) {
			$count++;
		}
		return $count;
	}

	/**
	 * Sets a database connection
	 *
	 * <p>Using Zend_Db_Adapter_Pdo_Mysql allows for more
	 *  database operations than Db_Pdo library.</p>
	 *
	 * @return Zend_Db_Adapter_Pdo_Mysql
	 */
	private function _db()
	{
		if (Zend_Registry::isRegistered('db') && Zend_Registry::get('db') instanceof Zend_Db_Adapter_Pdo_Abstract) {
			$db = Zend_Registry::get('db');
			return $db;
		}
	}

	/**
	 * Validates the request params.
	 *
	 * <p>Runs <code>$this->submitted_parameters</code>
	 * through each of the filters. Then does the same for
	 * validators. <p>
	 * <p>To catch empty params as FALSE, it will write FALSE
	 * for any filter params that are not submitted.</p>
	 * <p>To get a complete list of validated and escaped params,
	 * all params must be sent through the validator. If a param is
	 * in the filter arry and not the validator array, it will not be
	 * accessable through <code>$this->valid_parameters</code>
	 * </p>
	 *
	 */

	private function _validateParams()
	{
		//filter - do this separately from Zend_Filter_Input because it doesn't work right - the validator process is converting bool to string, so we have to recast it.
		$filteredParams = (array)$this->_request->submitted_parameters;
		if ($this->_filters)
			foreach ($this->_filters as $key => $value) {
				if (isset($this->_request->submitted_parameters->$key))
					$filteredParams[$key] = $value->filter($this->_request->submitted_parameters->$key);
				else
					//TODO: see if this will cause problems with other param types that are omited
					$filteredParams[$key] = FALSE;
			}

		//validate
		if ($this->_validators)
		{
			//note: The filter param is intentionally left NULL as Zend_Filter_Input will convert bool to string. So we handle this separtely.
			$validatedInput = new Zend_Filter_Input(NULL, $this->_validators, $filteredParams);

			if ($validatedInput->isValid()) {
				//get request
				$this->_request->valid_parameters = $validatedInput->getEscaped();

				//recast bools
				foreach ($this->_request->valid_parameters as $key => $value) {
					if (is_bool($filteredParams[$key]))
					{
						$this->_request->valid_parameters[$key] = (bool)$value;
					}
				}
			} else {
				$this->errors = $this->_prepareFailedParameterResponse($validatedInput);
			}
		}
	}

	/**
	 * Prepares an error for situations where the parameter validation fails.
	 *
	 * @param Zend_Filter_Input $validated_input
	 * @return Ssmart_EndpointError
	 */
	private function _prepareFailedParameterResponse($validated_input)
	{
		$endpointError = new Ssmart_EndpointError("failed_validation");

		if ($validated_input->hasInvalid())
		{
			$invalid = $validated_input->getInvalid();
			foreach ($invalid as $paramName => $paramArray)
			{
				foreach ($paramArray as $key => $value)
				{
					$error = new stdClass();
					$error->$key = $value;
					$endpointError->addError($error, $paramName);
				}
			}
		}
		if ($validated_input->hasMissing())
		{
			$missing = $validated_input->getMissing();
			foreach ($missing as $key => $value) {
				$error = new stdClass();
				$error->missing = $value;
				$endpointError->addError($error, $key);
			}
		}
		if ($validated_input->hasUnknown())
		{
			$unknown = $validated_input->getUnknown();
			foreach ($unknown as $key => $value) {
				//TODO: filter/escape things that need don't get validated
			}
		}
		return $endpointError;
	}

	/**
	 * Sets the total number of records returned in this request.
	 *
	 */
	private function _setRecordsReturned()
	{
		if ($this->records instanceof stdClass)
		{
			$this->recordsReturned = $this->_countResults($this->records);
		} elseif (is_array($this->records)) {
			$this->recordsReturned =  count($this->records);
		} else {
			$this->recordsReturned =   1;
		}
	}

	/**
	 * Sets the page number being requested.
	 *
	 */
	private function _setPageNumber()
	{
		$this->pageNumber = isset($this->_request->submitted_parameters->page_number) ? (int)$this->_request->submitted_parameters->page_number : $this->_default_page_number;
	}

	/**
	 * Sets the number of results per page requested.
	 *
	 */
	private function _setResultsPerPage()
	{
		if (isset($this->_request->submitted_parameters->results_per_page) && $this->_request->submitted_parameters->results_per_page > 0)
			$this->resultsPerPage = (int)$this->_request->submitted_parameters->results_per_page;
		else
			$this->resultsPerPage = (int)$this->_default_results_per_page;
	}

	/**
	 * Sets the total number of pages possible for this request.
	 *
	 */
	private function _setTotalPages()
	{
		$this->totalPages = $this->_getTotalPages($this->totalRecords, $this->resultsPerPage);
		//$this->totalPages = (int)(($this->totalRecords / $this->resultsPerPage) + (($this->totalRecords % $this->resultsPerPage) > 0 ? 1 : 0));
	}

	private function _getTotalPages($totalRecords, $resultsPerPage)
	{
		if ((int)$resultsPerPage != 0)
			return (int)($totalRecords/$resultsPerPage + (($totalRecords % $resultsPerPage) > 0 ? 1 : 0));
	}
}