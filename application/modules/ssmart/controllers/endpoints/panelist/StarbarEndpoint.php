<?php
/**
 * <p>Starbar endpoiints</p>
 *
 * @package Ssmart
 * @subpackage endpoint
 */
class Ssmart_Panelist_StarbarEndpoint extends Ssmart_GlobalController
{
	/**
	 * Gets the starbar data
	 *
	 * <p><b>required params: </b>
	 *	starbar_id</p>
	 *
	 * @param Ssmart_EndpointRequest $request
	 * @return \Ssmart_EndpointResponse
	 */
	public function getStarbar(Ssmart_EndpointRequest $request)
	{
		$validators = array(
				"starbar_id"		=> "int_required_notEmpty"
			);
		$filters = array();

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		//logic
		$starbarId				= $request->getParam("starbar_id");
		$userId					= $request->getUserId();

		//ensure this user has access to this starbar
		$this->checkUserAccessToStarbar($response, $starbarId, TRUE);

		$starbarObject = new Starbar();
		$starbarObject->loadData($starbarId);
		$starbarDataArray = array($starbarId => $starbarObject->getData());

		$economyId = $starbarDataArray[$starbarId]["economy_id"];

		//add starbar data to the response
		$response->addRecordsFromArray($starbarDataArray);

		//add game data to the response
		$commonDataParams = array("user_id" => $userId, "economy_id" => $economyId);
		$response->addCommonData("game", $commonDataParams);

		return $response;
	}

	/**
	 * Gets the available starbars.
	 *
	 * <p><b>required params: </b>
	 *	active_starbar</p>
	 *
	 * @param Ssmart_EndpointRequest $request
	 * @return \Ssmart_EndpointResponse
	 * @todo add pagination
	 */
	public function getAvailableStarbars(Ssmart_EndpointRequest $request)
	{
		$validators = array(
				"active_starbar"			=> "int_required_notEmpty"
			);
		$filters = array();

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		//logic
		$activeSarbarId				= $request->getParam("active_starbar");

		$starbars = $this->auth->user_data->starbars;
		if ($starbars[$activeSarbarId])
			unset($starbars[$activeSarbarId]);

		$response->addRecordsFromArray($starbars);

		return $response;
	}

	/**
	 * Subscribes a user to a starbar
	 *
	 * <p><b>required params: </b>
	 *	starbar_id</p>
	 *
	 * @param Ssmart_EndpointRequest $request
	 * @return \Ssmart_EndpointResponse
	 */
	public function subscribeStarbar(Ssmart_EndpointRequest $request)
	{
		$validators = array(
				"starbar_id"			=> "int_required_notEmpty"
			);
		$filters = array();

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		//logic
		$starbarId				= $request->getParam("starbar_id");
		$userId					= $request->getUserId();

		//ensure this user has access to this starbar
		$this->checkUserAccessToStarbar($response, $starbarId);

		//get starbar object
		$newStarbar = new Starbar();
		$newStarbar->loadData($starbarId);

		$userState = new User_State();
		$userState->loadDataByUniqueFields(array('user_id' => $userId));
		$status = $userState->addStarbar($newStarbar, NULL) ? "inserted" : "activated";

		$response->setResultVariable("status", $status);

		return $response;
	}

	/**
	 * Processes actions when a user shares a starbar.
	 *
	 * <p><b>required params: </b>
	 *	starbar_id
	 *	network</p>
	 *
	 * @param Ssmart_EndpointRequest $request
	 * @return \Ssmart_EndpointResponse
	 */
	public function shareStarbar(Ssmart_EndpointRequest $request)
	{
		$validators = array(
				"starbar_id"			=> "int_required_notEmpty",
				"network"				=> "alpha_required_notEmpty"
			);
		$filters = array();

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		//logic
		$network				= $request->getParam("network");
		$starbarId				= $request->getParam("starbar_id");
		$userId					= $request->getUserId();
		$economyId				= Economy::getIdforStarbar($starbarId);

		$shareResult = Game_Transaction::share($userId, $starbarId, "starbar", $network);

		$response->setResultVariable("transaction_id", $shareResult);

		//add game data to the response
		$commonDataParams = array("user_id" => $userId, "economy_id" => $economyId);
		$response->addCommonData("game", $commonDataParams);

		return $response;
	}

}