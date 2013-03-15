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
	public  function getAllUsers($params)
	{
		//define custom validators and filters

		//validate
		$preProcess = $this->_preProcess($params);

		//validate
		if (!$preProcess)
			return _prepareError(get_class() . "_failed", "Failed to get valid params from validator.");
		//check for validation errors
		if (isset($preProcess->error))
			return $preProcess;

		//logic
		$sql = "SELECT *
				FROM user
			";
		$sql .= $this->_prepareLimitSql((int)$preProcess["results_per_page"], (int)$preProcess["page_number"]);

		//TODO: try catch?
		$data = Db_Pdo::fetchAll($sql);

		//count logic
		$totalResults = $this->_countResults($data);

		//processes the logic and adds the pagination stuff
		$resultSet = $this->_prepareResponse($data, $preProcess, $totalResults);

		return $resultSet;
	}
}