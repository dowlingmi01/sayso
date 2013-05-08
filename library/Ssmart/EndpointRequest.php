<?php
/**
 * <p>Sets up the default structure and default values for the
 * endpoint request object</p>
 *
 * @package Api3
 * @subpackage endpoint
 */
class Api3_EndpointRequest
{
	/**
	 *The auth object
	 *
	 * <p>This is typically set elsewhere. However, when calling
	 *  one endpoint from another, we have to emulate the request .
	 * as the api sends it in.</p>
	 *
	 * @var Api3_Authentication (or child of)
	 */
	public $auth;

	/**
	 *Holds the parameters validated by the validation process.
	 *
	 * @var array
	 */
	public $validParameters;

	/**
	 * Load params into the request object.
	 *
	 * </p>This function is used to call one endpoint from aonther endpoint.</p>
	 * </p>A new Api3_request object must be created to pass into the other endpoint.</p>
	 *
	 * @param array $params
	 * @param Api3_Authentication $auth
	 */
	public function loadParams($params, $auth)
	{
		if (is_array($params))
		{
			foreach ($params as $key => $value) {
				$this->submittedParameters->$key = $value;
			}
		}
		//TODO: add processing for other input types.

		$this->auth = $auth;
	}
}