<?php

class Api3_SurveyController extends Api3_GlobalController
{
	/**returns all surveys with optional pagination
	 *
	 * this is more an easy test case to prove the concept
	 * than a usable example
	 *
	 * @param \stdClass $params
	 * @return int|\stdClass
	 */
	public  function getAllSurveys($params)
	{
		//define custom validators and filters

		$preProcess = $this->_preProcess($params);

		//validate
		if (!$preProcess)
			return _prepareError(get_class() . "_failed", "Failed to get valid params from validator.");
		//check for validation errors
		if (isset($preProcess->error))
			return $preProcess;

		//logic
		$sql = "SELECT *
				FROM survey
			";
		$sql .= $this->_prepareLimitSql((int)$preProcess["results_per_page"], (int)$preProcess["page_number"]);

		$data = Db_Pdo::fetchAll($sql);

		//count logic
		$totalResults = $this->_countResults($data);

		//processes the logic and adds the pagination stuff
		$resultSet = $this->_prepareResponse($data, $preProcess, $totalResults);

		return $resultSet;
	}
}
