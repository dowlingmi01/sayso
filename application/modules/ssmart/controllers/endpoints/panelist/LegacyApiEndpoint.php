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

		if ($response->hasErrors())
			return $response;

		$parameters = ['user_id'=>$request->auth->user_data->user_id, 'user_key'=>'unused'];
		if( isset($request->submitted_parameters->parameters) )
			foreach( $request->submitted_parameters->parameters as $key=>$value )
				$parameters[$key] = $value;
		$result = Api_Adapter::getInstance()->call($request->submitted_parameters->legacy_class,
			$request->submitted_parameters->legacy_action, $parameters);
		if($result instanceof Collection)
			$response->addRecordsFromCollection($result);

		// success
		return $response;
	}
}