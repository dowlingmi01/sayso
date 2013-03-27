<?php
/**
 * <p>Survey endpoiints</p>
 *
 * @package Api3
 * @subpackage endpoint
 */

class Api3_SurveyController extends Api3_GlobalController
{
	/**
	 * Returns all surveys with optional pagination
	 *
	 * <p>this is more an easy test case to prove the concept
	 * than a usable example</p>
	 *
	 * @param Api3_EndpointRequest $request
	 * @return Api3_EndpointResponse
	 */
	public  function getAllSurveys(Api3_EndpointRequest $request)
	{
		$response = new Api3_EndpointResponse($request);

		$request->addValidators(array("page_number" => "int_required_notEmpty", "results_per_page" => "int_required_notEmpty"));

		$request->preProcess();

		if ($request->hasErrors())
			return $response->addError();

		//logic
		$sql = "SELECT *
				FROM survey
			";
		$sql .= $this->_prepareLimitSql((int)$request->validParameters["results_per_page"], (int)$request->validParameters["page_number"]);

		//count logic
		$db = Zend_Registry::get('db');
		$count = $db->fetchOne("SELECT count(id) FROM survey");

		$response->addRecordsFromSql($sql);
		$response->addPagination($count);

		if ($request->hasErrors())
			return $response->addError();

		return $response;
	}
}
