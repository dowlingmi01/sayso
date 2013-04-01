<?php
/**
 * <p>Sets up the default structure and default values for the
 * endpoint response object</p>
 * <p>Provides endpoint response related functions and accessors.</p>
 *
 * @package Api3
 * @subpackage endpoint
 */
class Api3_EndpointResponse
{
	/**
	 *Holds the request data.
	 *
	 * @var Api3_EndpointRequest
	 */
	private $_request;

	/**
	 *Sets a default page number for the pagination functions.
	 *
	 * @var int
	 */
	private $_dafaultPageNumber = 1;

	/**
	 * Sets the default results per page for the pagination functions.
	 *
	 * @var int
	 */
	private $_dafaultResultsPerPage = 50;

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

//////////////////////////////////////////////////////

	/**
	 * Constructor
	 *
	 * <p>Sets the request object to be available in the response object.</p>
	 *
	 * @param Api3_EndpointRequest $request
	 */
	public function __construct($request, $filters = NULL, $validators = NULL) {
		$this->_request = $request;
		$this->setFilters($filters);
		$this->setValidators($validators);
		$this->_validateParams();
	}

	/**
	 * Adds the validators to the request object.
	 *
	 * @param array $validatorsArray
	 */
	public function setValidators($validatorsArray)
	{
		$this->_validators = $validatorsArray ? Api3_EndpointValidator::getValidators($validatorsArray) : "";
	}

	/**
	 * Adds the filters to the request object.
	 *
	 * @param array $filtersArray
	 */
	public function setFilters($filtersArray)
	{
		$this->_filters = $filtersArray ? Api3_EndpointValidator::getFilters($filtersArray) : "";
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
		$this->variables->$name = $value;
	}

	public function addContexts()
	{

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
	 * @param mixed $customError The error to display. It can be a string, array, or object.
	 */

	/**
	 * Sets an error in the response object.
	 *
	 * @param mixed $customError Can be string, array, or object.
	 * @throws Exception
	 */
	public function setResponseError($customError)
	{
		if (is_string($customError))
		{
			$this->errors = new Api3_EndpointError($customError);
			$this->errors->addError($customError);
		} elseif (is_array($customError)) {
			$this->errors = new Api3_EndpointError($customError["code"]);
			$this->errors->addError($customError["message"]);
		} elseif (is_object($customError)) {
			$this->errors = new Api3_EndpointError($customError->code);
			$this->errors->addError($customError->message);
		} else {
			throw new Exception("You must provide a proper error structure.");
		}
	}

	/**
	 * Add records to the response object from a
	 * prepared SQL statement.
	 *
	 * @param string $sql (valid SQL statement)
	 */
	public function addRecordsFromSql($sql)
	{
		$db = $this->_db();
		$sqlResponse = $db->fetchAssoc($sql);
		if (!$sqlResponse)
			//TODO: test mysql_error() handling.
			$this->addError("SQL Error", mysql_error());
		else
			$this->addRecordsFromArray ($sqlResponse);
	}

	/**
	 * Adds records to the response object from an array.
	 *
	 * @param array $array
	 */
	public function addRecordsFromArray($array)
	{
		foreach ($array as $key => $value) {
			$this->records->$key = $value;
		}
	}

	/**
	 * Get the response of another endpoint.
	 *
	 * @param type $action
	 * @param type $class
	 * @param type $params
	 * @param type $requestName
	 * @return Api3_EndpointResponse
	 * @throws Exception
	 */
	public function getFromOtherEndpoint($action, $class, $params, $requestName)
	{
		//TODO: validate action authentication.
		//format $params
		$request = new Api3_EndpointRequest();
		$request->loadParams($params, $this->_request->auth);

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

		if (isset($otherEndpointResponse->contexts))
			$this->contexts = $otherEndpointResponse->contexts;
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

		//TODO: add pagination
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
		//records returned
		$this->_setRecordsReturned();
		//total records
		$this->totalRecords = $count;
		//page number
		$this->_setPageNumber();
		//results per page
		$this->_setResultsPerPage();
		//total pages
		$this->_setTotalPages();
	}

	/**
	 * Runs the sql to count total possible results.
	 * Then calls setPagination
	 *
	 * @param string $sql SQL formatted string
	 */
	public function setPaginationBySql($sql)
	{
		$db = Zend_Registry::get('db');
		$count = $db->fetchOne($sql);
		$this->setPagination($count);
	}

	public function getRecords()
	{

	}

	public function addRecord()
	{

	}

	public function addRecords()
	{

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
	 *  database opperations than Db_Pdo library.</p>
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
	 * <p>Runs <code>$this->submittedParameters</code>
	 * through each of the filters. Then does the same for
	 * validators. <p>
	 * <p>To catch empty params as FALSE, it will write FALSE
	 * for any filter params that are not submitted.</p>
	 * <p>To get a complete list of validated and escaped params,
	 * all params must be sent through the validator. If a param is
	 * in the filter arry and not the validator array, it will not be
	 * accessable through <code>$this->validParameters</code>
	 * </p>
	 *
	 */

	private function _validateParams()
	{
		//filter - do this separately from Zend_Filter_Input because it doesn't work right - the validator process is converting bool to string, so we have to recast it.
		$filteredParams = (array)$this->_request->submittedParameters;
		if ($this->_filters)
			foreach ($this->_filters as $key => $value) {
				if (isset($this->_request->submittedParameters->$key))
					$filteredParams[$key] = $value->filter($this->_request->submittedParameters->$key);
				else
					//TODO: see if this will cause problems with other param types that are omited
					$filteredParams[$key] = FALSE;
			}

		//validate
		//note: The filter param is intentionally left NULL as Zend_Filter_Input will convert bool to string. So we handle this separtely.
		$validatedInput = new Zend_Filter_Input(NULL, $this->_validators, $filteredParams);

		if ($validatedInput->isValid()) {
			//get request
			$this->_request->validParameters = $validatedInput->getEscaped();

			//recast bools
			foreach ($this->_request->validParameters as $key => $value) {
				if (is_bool($filteredParams[$key]))
				{
					$this->_request->validParameters[$key] = (bool)$value;
				}
			}
		} else {
			$this->errors = $this->_prepareFailedParameterResponse($validatedInput);
		}
	}

	/**
	 * Prepares an error for situations where the parameter validation fails.
	 *
	 * @param Zend_Filter_Input $validated_input
	 * @return Api3_EndpointError
	 */
	private function _prepareFailedParameterResponse($validated_input)
	{
		$endpointError = new Api3_EndpointError("failed_validation");

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
	 * Sets the total number of records returned in this request.
	 *
	 */
	private function _setRecordsReturned()
	{
		if ($this->records instanceof stdClass)
		{
			$this->recordsReturned = $this->_countResults($this->records);
		} elseif (is_array($data)) {
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
		$this->pageNumber = isset($this->_request->submittedParameters->page_number) ? (int)$this->_request->submittedParameters->page_number : $this->_dafaultPageNumber;
	}

	/**
	 * Sets the number of results per page requested.
	 *
	 */
	private function _setResultsPerPage()
	{
		if (isset($this->_request->submittedParameters->results_per_page) && $this->_request->submittedParameters->results_per_page > 0)
			$this->resultsPerPage = $this->_request->submittedParameters->results_per_page;
		else
			$this->resultsPerPage = $this->_dafaultResultsPerPage;
	}

	/**
	 * Sets the total number of pages possible for this request.
	 *
	 */
	private function _setTotalPages()
	{
		$this->totalPages = (int)($this->totalRecords / $this->resultsPerPage) + (($this->totalRecords % $this->resultsPerPage) > 0 ? 1 : 0);
	}
}