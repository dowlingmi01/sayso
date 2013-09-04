<?php
/**
 * <p>Markup endpoints</p>
 *
 * @package Ssmart
 * @subpackage endpoint
 */
class Ssmart_Panelist_MarkupEndpoint extends Ssmart_GlobalController
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
			"key" => "required",
			"app" => "required"
		];
		$filters = [];

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		//logic
		$starbarId = $request->getparam("starbar_id");
		$app = $request->getParam("app");
		$key = $request->getParam("key");
		$userType = $request->getUserType();

		if (!in_array($app, ["browserapp", "webportal"])) {
			$response->setResponseError("invalid_markup_request");
			return $response;
		}

		$markup = Markup::getMarkup($userType, $app, $key, $starbarId);

		if ($markup === false) {
			$response->setResponseError("markup_unavailable");
		} else {
			$response->setResultVariable("markup", $markup);
		}

		return $response;
	}

}
