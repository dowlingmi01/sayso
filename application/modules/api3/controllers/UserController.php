<?php
/**
 * <p>User endpoiints</p>
 *
 * @package Api3
 * @subpackage endpoint
 */

class Api3_UserController extends Api3_GlobalController
{
	/**
	 * Returns all users with optional pagination
	 *
	 * <p>this is more an easy test case to prove the concept
	 * than a usable example</p>
	 *
	 * @param Api3_EndpointRequest $request
	 * @return Api3_EndpointResponse
	 */
	public  function getAllUsers(Api3_EndpointRequest $request)
	{
		$validators = array("page_number" => "int_required_notEmpty", "results_per_page" => "int_required_notEmpty");

		$response = new Api3_EndpointResponse($request, NULL, $validators);

		//logic
		$sql = "SELECT *
				FROM user
			";
		$sql .= $this->_prepareLimitSql((int)$request->validParameters["results_per_page"], (int)$request->validParameters["page_number"]);

		//count logic
		$countSql = "SELECT count(id) FROM user";

		$response->addRecordsFromSql($sql);
		$response->setPaginationBySql($countSql);

		return $response;
	}
}