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
	 * this can't be set here as some settings are (forgot the word)
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

	/**defines a default response node
	 *
	 * @var int
	 */
	protected $_records_returned;

	/**defines a default response node
	 *
	 * @var int
	 */
	protected $_total_records;

	/**defines a default response node
	 *
	 * @var int
	 */
	protected $_page_number;

	/**defines a default response node
	 *
	 * @var int
	 */
	protected $_total_pages;

	/**defines a default response node
	 *
	 * @var int
	 */
	protected $_results_per_page;

//////////////////////////////////////////////

	/**
	 * constructor
	 *
	 * sets the default _validators array
	 */
	public function __construct()
	{
		$this->_validators = array(
							"results_per_page" => array(
												new Zend_Validate_Int(),
												"allowEmpty" => true
											),
							"page_number" => array(
												new Zend_Validate_Int(),
												"allowEmpty" => true
											)
						);
	}

	/**returns the validated and filtered params
	 *
	 * @param array $filters
	 * @param array $validators
	 * @param  \stdClass $params
	 * @return Api3_ApiError | array
	 */
	public function getValidParams($filters, $validators, $params)
	{
		$validated_input = new Zend_Filter_Input($filters, $validators, (array)$params);

		if ($validated_input->isValid()) {
			//get request
			return $validated_input->getEscaped();
		} else {
			//TODO: send back specific error message from Zend_Filter_Input
			return Api3_ApiError::newError("endpoint_parameter_validation_failed");
		}
	}

	/**sets and calculates default responses (pagination)
	 * formats the endpoint response for return to the Api object
	 *
	 * @param mixed $data
	 * @param array $params
	 * @param int $count - the total records possible without pagination
	 * @return  \stdClass
	 */
	protected function _prepareResponse($data, $params, $count)
	{
		//records returned
		$response->records_reurned = $this->_records_returned = count($data);

		//total records
		$response->total_records = $this->_total_records = $count;

		//page number
		$response->page_number = $this->_page_number = isset($params["page_number"]) ? (int)$params["page_number"] : 1;

		//results per page
		$response->results_per_page = $this->_results_per_page = is_int((int)$params["results_per_page"]) && (int)$params["results_per_page"] > 0 ? (int)$params["results_per_page"] : $this->_total_records;

		//total pages
		$response->total_pages = $this->_total_pages = (int)($this->_total_records / $this->_results_per_page) + (($this->_total_records % $this->_results_per_page) > 0 ? 1 : 0);

		foreach($data as $record)
		{
			$key = $record[$this->_key_identifier];
			$response->records->$key = $record;
		}

		return $response;
	}

	/**calculates the limit based on $results_per_page
	 *
	 * @param type $results_per_page
	 * @return int | string="all"
	 */
	protected function _calculateLimit($results_per_page)
	{
		return isset($results_per_page) && $results_per_page != 0 ? (int)$results_per_page : "all";
	}

	/**calculate offset based on the page number and limit
	 *
	 * @param int $page_number
	 * @param int $limit
	 * @return int
	 */
	protected function _calculateOffset($page_number, $limit)
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
	}}