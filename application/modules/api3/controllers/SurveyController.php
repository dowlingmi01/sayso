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

		//validate
		$actionParams = $this->getValidParams($this->_filters, $this->_validators, $params);

		//check for validation errors
		if (!$actionParams instanceof Api3_ApiError)
		{
			//logic
			$sql = "SELECT *
					FROM survey
				";

			$limit = $this->_calculateLimit((int)$actionParams["results_per_page"]);
			$offset = $this->_calculateOffset((int)$actionParams["page_number"], $limit);

			if ($limit != "all")
			{
				$sql .= " LIMIT {$offset}, {$limit}";
			}

			$data = Db_Pdo::fetchAll($sql);

			//count logic
			$sql = "SELECT count(id) c
					FROM survey";
			$data2 = Db_Pdo::fetch($sql);
			$totalResults = (int)$data2["c"];

			//processes the logic and adds the pagination stuff
			$resultSet = $this->_prepareResponse($data, $actionParams, $totalResults);

			return $resultSet;
		} else {
			return $actionParams;
		}
	}
}
