<?php

class Api3_UserController extends Api3_GlobalController
{
	/**returns all users with optional pagination
	 *
	 * this is more an easy test case to prove the concept
	 * than a usable example
	 *
	 * @param \stdClass $params
	 * @return int|\stdClass
	 */
	public  function getAllUsers($params, $request_name)
	{
		//define custom validators and filters

		//validate
		if ($actionParams = $this->getValidParams($this->_filters, $this->_validators, $params, $request_name))
		{
		//check for validation errors
			if (!$actionParams instanceof Api3_ApiError)
			{
				//logic
				$sql = "SELECT *
						FROM user
					";
				$sql .= $this->_prepareLimitSql((int)$actionParams["results_per_page"], (int)$actionParams["page_number"]);

				//TODO: try catch?
				$data = Db_Pdo::fetchAll($sql);

				//count logic
				$sql = "SELECT count(id) c
						FROM user";
				$data2 = Db_Pdo::fetch($sql);
				$totalResults = (int)$data2["c"];

				//processes the logic and adds the pagination stuff
				$resultSet = $this->_prepareResponse($data, $actionParams, $totalResults);

				return $resultSet;
			} else {
				return $actionParams;
			}
		} else {
			return Api3_ApiError::getNewError("endpoint_failed", $request_name);
		}
	}
}