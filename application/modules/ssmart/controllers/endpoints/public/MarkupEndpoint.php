<?php
/**
 * <p>Markup endpoints</p>
 *
 * @package Ssmart
 * @subpackage endpoint
 */
class Ssmart_Public_MarkupEndpoint extends Ssmart_GlobalController
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

	public function getMarkup(Ssmart_EndpointRequest $request)
	{
		$validators = [
			"starbar_id" => "int_required_notEmpty",
			"key" => "alpha_required_notEmpty"
		];
		$filters = [];

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		//logic
		$starbarId = $request->valid_parameters["starbar_id"];
		$userId	= $request->auth->user_data->user_id;

		//ensure this user has access to this starbar
		$this->checkUserAccessToStarbar($response, $starbarId, TRUE);

		if ($response->hasErrors())
			return $response;

		$markup = Markup::getMarkup($request->auth->user_type, "webportal", $request->valid_parameters["key"], $starbarId);

		if ($markup === false) {
			$response->setResponseError("markup_unavailable");
		} else {
			$response->setResultVariable("markup", $markup);
		}

		return $response;
	}

}
