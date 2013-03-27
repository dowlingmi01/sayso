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
	 * Constructor
	 *
	 * <p>Sets the request object to be available in the response object.</p>
	 *
	 * @param Api3_EndpointRequest $request
	 */
	public function __construct($request) {
		$this->_request = $request;
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

	public function addRecord()
	{

	}

	public function addRecords()
	{

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
	public function addContext($name, $value)
	{
		$this->contexts->$name = $value;
	}

	public function addContexts()
	{

	}

	/**
	 * Adds an error to the response object.
	 *
	 * <p>The value of <code>$customError</code> can be a string, array, or object structure.</p>
	 * <p>It will be passed as it is to the api output.</p>
	 *
	 * @param mixed $customError
	 * @return \Api3_EndpointResponse
	 */
	public function addError($customError = NULL)
	{
		$this->errors = new Api3_EndpointError();

		if (!$customError)
		{
			$errorName = $this->_request->error->meta->errorName;
			$errorMessage = $this->_request->error->errors;
		} else {
			$errorName = $customError["code"];
			$errorMessage = $customError["message"];
		}
		$this->errors->meta->errorName =$errorName;
		$this->errors->{$this->errors->meta->errorName} = $errorMessage;
		return $this;
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
	 * Adds the response of another endpoint to the response of this endpoint.
	 *
	 * @param string $action The name of the action in the other endpoint to call.
	 * @param string $class The name of the class that holds the other endpoint.
	 * @param array $params The params to be passed to the other endpoint.
	 * @param string $requestName The name of the api request.
	 */
	public function addFromOtherEndpoint($action, $class, $params, $requestName)
	{
		//TODO: validate action authentication.
		//format $params
		$request = new Api3_EndpointRequest();
		$request->loadParams($params, $this->_request->auth);

		$otherClass = new $class($requestName, $this->_request->auth);
		$otherEndpointResponse = $otherEndpointResult = $otherClass->$action($request);

		if (isset($otherEndpointResponse->contexts))
			$this->contexts = $otherEndpointResponse->contexts;
		if (isset($otherEndpointResponse->records))
			$this->records = $otherEndpointResponse->records;

		//TODO: check for errors from other endpoint
	}

	public function getRecords()
	{

	}

	/**
	 * Check to see of this response has processed any errors yet.
	 *
	 * @return boolean
	 */
	public function hasErrors()
	{
		if (isset($this->error))
		{
			return TRUE;
		}
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
	 * Calls the pagination setter functions
	 *
	 * @param int $count - the total records possible without pagination
	 */
	public function addPagination($count)
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