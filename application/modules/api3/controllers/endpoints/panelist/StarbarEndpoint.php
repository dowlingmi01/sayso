<?php
/**
 * <p>Starbar endpoiints</p>
 *
 * @package Api3
 * @subpackage endpoint
 */
class Api3_StarbarEndpoint extends Api3_GlobalController
{
	/**
	 * Gets the starbar data
	 *
	 * <p><b>required params: </b>
	 *	starbar_id</p>
	 *
	 * @param Api3_EndpointRequest $request
	 * @return \Api3_EndpointResponse
	 */
	public function getStarbar(Api3_EndpointRequest $request)
	{
		$validators = array(
				"starbar_id"		=> "int_required_notEmpty"
			);
		$filters = array();

		$response = new Api3_EndpointResponse($request, $filters, $validators);

		//logic
		$starbarId				= $request->validParameters["starbar_id"];
		$userId				= $request->auth->userData->user_id;

		//ensure this user has access to this starbar
		$this->checkUserAccessToStarbar($response, $starbarId, TRUE);

		if ($response->hasErrors())
			return $response;

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
	 * @param Api3_EndpointRequest $request
	 * @return \Api3_EndpointResponse
	 */
	public function getAvailableStarbars(Api3_EndpointRequest $request)
	{
		$validators = array(
				"active_starbar"			=> "int_required_notEmpty"
			);
		$filters = array();

		$response = new Api3_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		$activeSarbarId				= $request->validParameters["active_starbar"];

		$starbars = $this->auth->userData->starbars;
		if ($starbars->{$activeSarbarId})
			unset($starbars->{$activeSarbarId});

		$response->addRecordsFromArray($starbars);

		return $response;
	}

	/**
	 * Subscribes a user to a starbar
	 *
	 * <p><b>required params: </b>
	 *	starbar_id</p>
	 *
	 * @param Api3_EndpointRequest $request
	 * @return \Api3_EndpointResponse
	 */
	public function subscribeStarbar(Api3_EndpointRequest $request)
	{
		$validators = array(
				"starbar_id"			=> "int_required_notEmpty"
			);
		$filters = array();

		$response = new Api3_EndpointResponse($request, $filters, $validators);

		//logic
		$starbarId					= $request->validParameters["starbar_id"];
		$userId					= $request->auth->userData->user_id;

		//ensure this user has access to this starbar
		$this->checkUserAccessToStarbar($response, $starbarId);

		if ($response->hasErrors())
			return $response;

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
	 * @param Api3_EndpointRequest $request
	 * @return \Api3_EndpointResponse
	 */
	public function shareStarbar(Api3_EndpointRequest $request)
	{
		$validators = array(
				"starbar_id"			=> "int_required_notEmpty",
				"network"				=> "alpha_required_notEmpty"
			);
		$filters = array();

		$response = new Api3_EndpointResponse($request, $filters, $validators);

		//logic
		$network					= $request->validParameters["network"];
		$starbarId					= $request->validParameters["starbar_id"];
		$userId					= $request->auth->userData->user_id;
		$economyId				= Economy::getIdforStarbar($starbarId);

		$shareResult = Game_Transaction::share($userId, $starbarId, "starbar", $network);

		$response->setResultVariable("transaction_id", $shareResult);

		//add game data to the response
		$commonDataParams = array("user_id" => $userId, "economy_id" => $economyId);
		$response->addCommonData("game", $commonDataParams);

		return $response;
	}

}