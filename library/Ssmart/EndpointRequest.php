<?php
/**
 * <p>Sets up the default structure and default values for the
 * endpoint request object</p>
 *
 * @package Ssmart
 * @subpackage endpoint
 */
class Ssmart_EndpointRequest
{
	/**
	 *The auth object
	 *
	 * <p>This is typically set elsewhere. However, when calling
	 *  one endpoint from another, we have to emulate the request .
	 * as the api sends it in.</p>
	 *
	 * @var Ssmart_Authentication (or child of)
	 */
	public $auth;

	/**
	 *Holds the parameters validated by the validation process.
	 *
	 * @var Object()
	 */
	public $valid_parameters;

	/**
	 *Holds the parameters validated by the validation process.
	 *
	 * @var Object()
	 */
	public $submitted_parameters;

	public function __construct() {
		$this->valid_parameters = new Object();
		$this->submitted_parameters = new Object();
	}

	/**
	 * Load params into the request object.
	 *
	 * </p>This function is used to call one endpoint from aonther endpoint.</p>
	 * </p>A new Ssmart_request object must be created to pass into the other endpoint.</p>
	 *
	 * @param array $params
	 * @param Ssmart_Authentication $auth
	 */
	public function loadParams($params, $auth)
	{
		if (is_array($params))
		{
			foreach ($params as $key => $value) {
				$this->submitted_parameters->$key = $value;
			}
		}
		//TODO: add processing for other input types.

		$this->auth = $auth;
	}
}