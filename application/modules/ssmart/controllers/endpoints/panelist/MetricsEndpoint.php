<?php
/**
 * <p>Metrics endpoints</p>
 *
 * @package Ssmart
 * @subpackage endpoint
 */
class Ssmart_Panelist_MetricsEndpoint extends Ssmart_GlobalController
{
	public function insertEvents(Ssmart_EndpointRequest $request){
		$validators = array(
			"events" => "required_notEmpty"
		);
		$filters = array();

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		//logic
		$userId = $request->getUserId();
		$sessionId = $request->getSessionId();
		$events = $request->getParam('events');
		$log = new Log_Event($userId, $sessionId);
		$result = $log->insert($events);

		$response->setResultVariable("success", $result);
		return $response;
	}
}