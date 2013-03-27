<?php
/**
 * <p>This class is to be a collection of endpoint generation tools.</p>
 *
 * @package Api3
 *
 */
class Api3_GlobalController //extends Zend_Controller_Action
{
	/**
	 * Variable to hold the validators.
	 *
	 * @var array
	 */
	protected  $_validators = array();

	/**
	 * Variable to hold the filters.
	 *
	 * @var array
	 */
	protected  $_filters =	array();

//////////////////////////////////////////////

	/**
	 * Sets the auth and request name.
	 *
	 */
	public function __construct($requestName, $auth)
	{
		$this->request_name = $requestName;
		$this->auth = $auth;
	}

	/**
	 * Calculates the limit based on <code>$results_per_page</code>
	 *
	 * @param int $results_per_page
	 * @return int|string
	 */
	private function _calculateLimit($results_per_page)
	{
		//TODO: evaluate the use of "all" here
		return isset($results_per_page) && $results_per_page != 0 ? (int)$results_per_page : "all";
	}

	/**
	 * Calculate offset based on the
	 * <code>$page_number</code> and <code>$limit</code>
	 *
	 * @param int $page_number
	 * @param int $limit
	 * @return int
	 */
	private function _calculateOffset($page_number, $limit)
	{
		return isset($page_number) ? ($page_number*$limit)-1 : 0;
	}

	/**
	 * Prepares the limit portion of a SQL statement by taking
	 * the<code>$results_per_page</code> and
	 * the <code>$page_number</code> parameters
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

}