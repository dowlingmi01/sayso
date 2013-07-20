<?php
/**
 * <p>Game endpoiints</p>
 *
 * @package Ssmart
 * @subpackage endpoint
 */
class Ssmart_Panelist_GameEndpoint extends Ssmart_GlobalController
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

		//logic
		$userId			= $request->getUserId();
		$starbarId		= $request->getParam("starbar_id");

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
     *    quantity
     *    game_asset_id
     *    starbar_id</p>
     *
     * @param Ssmart_EndpointRequest $request
     *
     * @throws Exception
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

		//logic
		$userId			    = $request->getUserId();
		$starbarId			= $request->getParam("starbar_id");
		$gameAssetId		= $request->getParam("game_asset_id");
		$quantity			= $request->getParam("quantity", 1);
		$economyId		    = Economy::getIdforStarbar($starbarId);

		try
		{
			$transactionId = Game_Transaction::run( $userId, $economyId, 'PURCHASE', array('game_asset_id'=>$gameAssetId, 'quantity'=>$quantity, 'starbar_id'=>$starbarId));
		} catch(Exception $e) {
			throw new Ssmart_EndpointError("Endpoint error", 'Game transaction failed - ' . $e->getMessage());
		}
		if ($transactionId)
		{
            //do the order processing now
            $orderData = array(
                "quantity"     => $quantity,
                "good_id"      => $gameAssetId,
                "user_id"      => $userId,
                "starbar_id"   => $starbarId,
                "game_txn_id"  => $transactionId
            );
			$orderData["shipping"] = $request->getParam("shipping");

            $order = new Game_Transaction_Order();
            //TODO: maybe a check to see if it succeeded?
            $order->processOrder($orderData);

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

		//logic
		$userId			= $request->getUserId();
		$starbarId		= $request->getParam("starbar_id");

		$goods = Game_Transaction::getPurchasablesForUser($userId, $starbarId);

		$response->addRecordsFromCollection($goods);

		return $response;
	}

}