<?php
/**
 * <p>Legacy Api endpoints</p>
 *
 * @package Ssmart
 * @subpackage endpoint
 */
class Ssmart_Panelist_LegacyApiEndpoint extends Ssmart_GlobalController
{
	public function call(Ssmart_EndpointRequest $request)
	{
		$validators = array(
			"legacy_class"			=> "required",
			"legacy_action"			=> "required"
		);
		$filters = array();

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		$legacyClass = $request->getParam("legacy_class");
		$legacyAction = $request->getParam("legacy_action");

		$parameters = ['user_id'=>$request->getUserId(), 'user_key'=>'unused'];
		if( $input = $request->getParam("parameters") )
			foreach( $input as $key=>$value )
				$parameters[$key] = $value;
		$result = Api_Adapter::getInstance()->call($legacyClass,
			$legacyAction, $parameters);
		if($result instanceof Collection)
			$response->addRecordsFromCollection($result);

		// success
		return $response;
	}
}