<?php
/**
 * this class is to be a collection of endpoint generation tools
 *
 */
class Api3_GlobalController //extends Zend_Controller_Action
{
	/**all response results are wrapped in keyed containers
	 * this defines the key for automating response and endpoint construction
	 *
	 * @var string
	 */
	protected  $_key_identifier = 'id';

	/**declares the _validators array
	 *
	 * this can't be set here as some settings are expressions
	 * so they are set in the constructor
	 *
	 * @var array
	 */
	protected  $_validators = array();

	/**declares the filters and sets the defaults
	 *
	 *
	 * @var array
	 */
	protected  $_filters =	array(
						'*'  => 'StringTrim',
					);

	/**declares records_returned
	 *
	 * @var int
	 */
	protected $_records_returned;

	/**declares total_records
	 *
	 * @var int
	 */
	protected $_total_records;

	/**declares page_number
	 *
	 * @var int
	 */
	protected $_page_number;

	/**declares total_pages
	 *
	 * @var int
	 */
	protected $_total_pages;

	/**declares results_per_page
	 *
	 * @var int
	 */
	protected $_results_per_page;

	/**defines a default response
	 *
	 * @var \stdClass
	 */
	protected $_results;

//////////////////////////////////////////////

	/**applies the session endpoint data to the object for ease of access
	 * populates the _validator as it takes expressions and cannot be set
	 *	in the declaration above
	 *
	 */
	public function __construct()
	{
		//get session data
		$endpoint = new Zend_Session_Namespace("endpoint");

		//sets the results node
		$this->_results = new stdClass();

		$this->request_name = $endpoint->request_name;
		$this->auth = $endpoint->auth;

		$this->_validators = array(
							"results_per_page" => array(
												new Zend_Validate_Int(),
												"allowEmpty" => FALSE
											),
							"page_number" => array(
												new Zend_Validate_Int(),
												"allowEmpty" => FALSE
											),
							"request_name" => array(
												new Zend_Validate_Int(),
												"allowEmpty" => FALSE
											),
						);
	}

	/**automates some of the post-construct functionality that all endpoints share
	 *	merges the default validators with the endpoint specific ones
	 *	merges the default filters with the endpoint specific ones
	 *	runs the validator function
	 *
	 *	returns the valid parameters
	 *
	 * @param array $data
	 * @param array $validators
	 * @param array $filters
	 * @return array
	 */
	protected function _preProcess($data, $validators = NULL, $filters = NULL)
	{
		//merge the user spcificed validators
		if ($validators)
			$this->_validators = array_merge($this->_validators, $validators);
		//merge the user specifiec filters
		if ($filters)
			$this->_filters = array_merge($this->_filters, $filters);
		//validate the parameters
		return $this->_getValidParams($this->_filters, $this->_validators, $data, $this->request_name);
	}

	/**seems like it should be used !?
	 *
	 */
	protected function _postProcess()
	{

	}

	/**returns the validated and filtered params
	 *
	 * @param array $filters
	 * @param array $validators
	 * @param  \stdClass $params
	 * @return Api3_ApiError | array
	 */
	private function _getValidParams($filters, $validators, $params, $request_name)
	{
		$validated_input = new Zend_Filter_Input($filters, $validators, (array)$params);
		$failedParams = array();

		if ($validated_input->isValid()) {
			//get request
			return $validated_input->getEscaped();
		} else {
			return $this->_prepareFailedParameterResponse($validated_input);
		}
	}

	/**sets and calculates default responses (pagination)
	 * formats the endpoint response for return to the Api object
	 *
	 * @param array $data
	 * @param array $params
	 * @param int $count - the total records possible without pagination
	 * @return  \stdClass
	 */
	protected function _prepareResponse($data, $params, $count = 1)
	{
		//records returned
		if ($data instanceof stdClass)
		{
			//iterate
			$recordsReturned = 0;
			foreach ($data as $key)
			{
				$recordsReturned++;
			}
			$response->records_reurned = $this->_records_returned = $recordsReturned;
		} elseif (is_array($data)) {
			$response->records_reurned = $this->_records_returned = count($data);
		} else {
			$response->records_reurned = $this->_records_returned = 1;
		}

		//total records
		$response->total_records = $this->_total_records = $count;

		//page number
		$response->page_number = $this->_page_number = isset($params["page_number"]) ? (int)$params["page_number"] : 1;

		//results per page
		$response->results_per_page = $this->_results_per_page = is_int((int)$params["results_per_page"]) && (int)$params["results_per_page"] > 0 ? (int)$params["results_per_page"] : $this->_total_records;

		//total pages
		$response->total_pages = $this->_total_pages = (int)($this->_total_records / $this->_results_per_page) + (($this->_total_records % $this->_results_per_page) > 0 ? 1 : 0);

		//TODO: make this more extensible to accept values that may not be part of the data set
		foreach($data as $record)
		{
			$key = $record[$this->_key_identifier];
			$response->records->$key = $record;
		}

		return $response;
	}

	/**
	 *
	 * @param Zend_Filter_Input $errors
	 */
	private function _prepareFailedParameterResponse($validated_input)
	{
		$response = new stdClass();
		$response->error = TRUE;
		$response->error_name = "failed_validation";

		if ($validated_input->hasInvalid())
		{
			$invalid = $validated_input->getInvalid();
			foreach ($invalid as $paramName => $paramArray) {

				foreach ($paramArray as $key => $value)
				{
					$response->errors->$paramName->$key = $value;
				}
			}
		}
		if ($validated_input->hasMissing())
		{
			$missing = $validated_input->getMissing();
			foreach ($missing as $key => $value) {
				$response->errors->$key->missing = $value;
			}
		}
		if ($validated_input->hasUnknown())
		{
			$unknown = $validated_input->getUnknown();
			foreach ($unknown as $key => $value) {
				//$response->$key->unknown = $value;
				//TODO: filter things that need don't get validated
			}
		}
		return $response;
	}

	/**sets a custom error
	 *
	 * @param array $error
	 */
	protected function _prepareError($errors)
	{
		$response = new stdClass();
		$response->error = TRUE;
		$response->error_name = "custom_error";
		foreach ($errors as $key => $value) {
			$response->errors->{$response->error_name}->{$errors["code"]} = $errors["message"];
		}
		return $response;
	}

	/**calculates the limit based on $results_per_page
	 *
	 * @param type $results_per_page
	 * @return int | string="all"
	 */
	private function _calculateLimit($results_per_page)
	{
		return isset($results_per_page) && $results_per_page != 0 ? (int)$results_per_page : "all";
	}

	/**calculate offset based on the page number and limit
	 *
	 * @param int $page_number
	 * @param int $limit
	 * @return int
	 */
	private function _calculateOffset($page_number, $limit)
	{
		return isset($page_number) ? ($page_number*$limit)-1 : 0;
	}

	/**prepares the limit SQL by taking the results per page and
	 * the page number parameters
	 *
	 * @param int $page_number
	 * @param int $results_per_page
	 * @return string
	 */
	protected function _prepareLimitSql($results_per_page, $page_number)
	{
		$limit = $this->_calculateLimit((int)$results_per_page);
		$offset = $this->_calculateOffset((int)$page_number, $limit);

		if (is_int($limit) && $limit > 0)
		{
			if ($offset < 0 || !is_int($offset))
			{
				$offset = 0;
			}
			return " LIMIT {$offset}, {$limit}";
		}
	}

	/**provides a tool for getting a count of results
	 * may not be applicable to all situations
	 *
	 * @param array | \stdClass $results
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
}