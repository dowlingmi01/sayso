<?php
/**
 * <p>Game endpoiints</p>
 *
 * @package Api3
 * @subpackage endpoint
 */
class Api3_GameEndpoint extends Api3_GlobalController
{
	/**
	 * Gets the game data
	 *
	 * <p><b>required params: </b>
	 *	starbar_id</p>
	 *
	 * @param Api3_EndpointRequest $request
	 * @return \Api3_EndpointResponse
	 */
	public function getGame(Api3_EndpointRequest $request)
	{
		$validators = array(
				"starbar_id"			=> "int_required_notEmpty",
			);
		$filters = array();

		$response = new Api3_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		$userId			= $request->auth->userData->user_id;
		$starbarId			= $request->validParameters["starbar_id"];

		$economyId = Economy::getIdforStarbar($starbarId);
		$commonDataParams = array("user_id" => $userId, "economy_id" => $economyId);
		$game = $response->getCommonDataFromModel("game", $commonDataParams);

		$response->addRecordsFromArray($game);

		return $response;
	}

	/**
	 * Processes a purchase transaction on the game object.
	 *
	 * <p><b>required params: </b>
	 *	quantity
	 *	game_asset_id
	 *	starbar_id</p>
	 *
	 * @param Api3_EndpointRequest $request
	 * @return \Api3_EndpointResponse
	 */
	public function redeemReward(Api3_EndpointRequest $request)
	{
		$validators = array(
				"starbar_id"			=> "int_required_notEmpty",
				"game_asset_id"			=> "int_required_notEmpty",
				"quantity"				=> "int"
			);
		$filters = array();

		$response = new Api3_EndpointResponse($request, $filters, $validators);


		if ($response->hasErrors())
			return $response;

		//logic
		$userId			= $request->auth->userData->user_id;
		$starbarId			= $request->validParameters["starbar_id"];
		$gameAssetId		= $request->validParameters["game_asset_id"];
		$quantity			= isset($request->validParameters["quantity"]) ? $request->validParameters["quantity"] : 1;
		$economyId		= Economy::getIdforStarbar($starbarId);


		$transactionId = Game_Transaction::run( $userId, $economyId, 'PURCHASE', array('game_asset_id'=>$gameAssetId, 'quantity'=>$quantity, 'starbar_id'=>$starbarId));

		if ($transactionId)
		{
			$response->setResultVariable("success", TRUE);
			$response->setResultVariable("transaction_id", $transactionId);
		} else {
			$response->setResultVariable("success", FALSE);
		}

		$commonDataParams = array("user_id" => $userId, "economy_id" => $economyId);
		$response->addCommonData("game", $commonDataParams);

		return $response;
	}

	/**
	 * Retrieves all of the items for the reward center.
	 *
	 * <p><b>required params: </b>
	 *	starbar_id</p>
	 * <b>optional params:</b>
	 *	results_per_page
	 *	page_number</p>
	 *
	 * @param Api3_EndpointRequest $request
	 * @return \Api3_EndpointResponse
	 */
	public function getStarbarGoods(Api3_EndpointRequest $request)
	{
		$validators = array(
				"starbar_id"			=> "int_required_notEmpty",
				"results_per_page"		=> "int_required_notEmpty",
				"page_number"			=> "int_required_notEmpty"
			);
		$filters = array();

		$response = new Api3_EndpointResponse($request, $filters, $validators);


		if ($response->hasErrors())
			return $response;

		//logic
		$userId			= $request->auth->userData->user_id;
		$starbarId			= $request->validParameters["starbar_id"];

		$goods = Game_Transaction::getPurchasablesForUser($userId, $starbarId);

		$response->addRecordsFromCollection($goods);

		return $response;
	}

}