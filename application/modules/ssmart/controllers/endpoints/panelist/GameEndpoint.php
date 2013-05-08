<?php
/**
 * <p>Game endpoiints</p>
 *
 * @package Ssmart
 * @subpackage endpoint
 */
class Ssmart_GameEndpoint extends Ssmart_GlobalController
{
	/**
	 * Gets the game data
	 *
	 * <p><b>required params: </b>
	 *	starbar_id</p>
	 *
	 * @param Ssmart_EndpointRequest $request
	 * @return \Ssmart_EndpointResponse
	 */
	public function getGame(Ssmart_EndpointRequest $request)
	{
		$validators = array(
				"starbar_id"			=> "int_required_notEmpty",
			);
		$filters = array();

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		$userId			= $request->auth->userData->user_id;
		$starbarId			= $request->validParameters["starbar_id"];

		$economyId = Economy::getIdforStarbar($starbarId);
		$commonDataParams = array("user_id" => $userId, "economy_id" => $economyId);
		$game = $response->getCommonDataFromModel("game", $commonDataParams);

		$response->setResultVariables($game);

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
	 * @param Ssmart_EndpointRequest $request
	 * @return \Ssmart_EndpointResponse
	 */
	public function redeemReward(Ssmart_EndpointRequest $request)
	{
		$validators = array(
				"starbar_id"			=> "int_required_notEmpty",
				"game_asset_id"			=> "int_required_notEmpty",
				"quantity"				=> "int"
			);
		$filters = array();

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);


		if ($response->hasErrors())
			return $response;

		//logic
		$userId			= $request->auth->userData->user_id;
		$starbarId			= $request->validParameters["starbar_id"];
		$gameAssetId		= $request->validParameters["game_asset_id"];
		$quantity			= isset($request->validParameters["quantity"]) ? $request->validParameters["quantity"] : 1;
		$economyId		= Economy::getIdforStarbar($starbarId);

		try
		{
			$transactionId = Game_Transaction::run( $userId, $economyId, 'PURCHASE', array('game_asset_id'=>$gameAssetId, 'quantity'=>$quantity, 'starbar_id'=>$starbarId));
		} catch(Exception $e) {
			throw new Exception('Game transaction failed.');
		}
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
	 * @param Ssmart_EndpointRequest $request
	 * @return \Ssmart_EndpointResponse
	 */
	public function getStarbarGoods(Ssmart_EndpointRequest $request)
	{
		$validators = array(
				"starbar_id"			=> "int_required_notEmpty",
				"results_per_page"		=> "int_required_notEmpty",
				"page_number"			=> "int_required_notEmpty"
			);
		$filters = array();

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);


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