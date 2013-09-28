<?php
/**
 * <p>This class is to be a collection of endpoint generation tools.</p>
 *
 * @package Ssmart
 *
 */
class Ssmart_GlobalController //extends Zend_Controller_Action
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
	 * Prepares the limit portion of a SQL statement by taking
	 * the<code>$results_per_page</code> and
	 * the <code>$page_number</code> parameters
	 *
	 * @param int $pageNumber
	 * @param int $resultsPerPage
	 * @return string
	 */
	protected function _prepareLimitSql($resultsPerPage, $pageNumber)
	{
		$offset = $this->_calculateOffset((int)$pageNumber, (int)$resultsPerPage);

		if ($resultsPerPage)
		{
			if ($offset < 0 || !is_int($offset))
			{
				$offset = 0;
			}
			return " LIMIT {$offset}, {$resultsPerPage}";
		}
	}

	protected function checkUserAccessToStarbar($response, $starbarId, $active = NULL, $data = array())
	{
		if (!isset($this->auth->user_data->starbars[$starbarId]))
			throw new Ssmart_EndpointError("user_does_not_have_access_to_starbar");
		else if ($active && (bool)$this->auth->user_data->starbars[$starbarId]['active'] !== $active)
			throw new Ssmart_EndpointError("starbar_not_active");
		else if (!$active)
			User::validateUserIdForStarbar($this->auth->user_data->user_id, $starbarId, $data);
	}

	/**
	 * Calculate offset based on $pageNumber and $resultsPerPage
	 *
	 * @param int $pageNumber
	 * @param int $resultsPerPage
	 * @return int
	 */
	protected function _calculateOffset($pageNumber = 1, $resultsPerPage = 50)
	{
		return ($pageNumber-1)*$resultsPerPage;
	}

}