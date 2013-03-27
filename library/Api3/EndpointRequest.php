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
	 *Filters to applied be to the request.
	 *
	 * @var array
	 */
	private $_filters;

	/**
	 *Validators to beapplied to the request.
	 *
	 * @var array
	 */
	private $_validators;

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
	 *Holds the parameters as they were submitted.
	 *
	 * @var array
	 */
	public $submittedParameters;

	/**
	 *Performs the initial processing of a request.
	 *
	 */
	public function preProcess()
	{
		//validate the parameters
		$this->_validateParams();
	}

	/**
	 * Validates the request params.
	 *
	 * <p>Runs <code>$this->submittedParameters</code>
	 * through each of the filters. Then does the same for
	 * validators. <p>
	 * <p>To catch empty params as FALSE, it will write FALSE
	 * for any filter params that are not submitted.</p>
	 * <p>To get a complete list of validated and escaped params,
	 * all params must be sent through the validator. If a param is
	 * in the filter arry and not the validator array, it will not be
	 * accessable through <code>$this->validParameters</code>
	 * </p>
	 *
	 * @return Api3_EndpointError | array
	 */
	private function _validateParams()
	{
		//filter - do this separately from Zend_Filter_Input because it doesn't work right - the validator process is converting bool to string, so we have to recast it.
		$filteredParams = (array)$this->submittedParameters;
		if ($this->_filters)
			foreach ($this->_filters as $key => $value) {
				if (isset($this->submittedParameters->$key))
					$filteredParams[$key] = $value->filter($this->submittedParameters->$key);
				else
					//TODO: see if this will cause problems with other param types that are omited
					$filteredParams[$key] = FALSE;
			}

		//validate
		//note: The filter param is intentionally left NULL as Zend_Filter_Input will convert bool to string. So we handle this separtely.
		$validatedInput = new Zend_Filter_Input(NULL, $this->_validators, $filteredParams);

		if ($validatedInput->isValid()) {
			//get request
			$this->validParameters = $validatedInput->getEscaped();

			//recast bools
			foreach ($this->validParameters as $key => $value) {
				if (is_bool($filteredParams[$key]))
				{
					$this->validParameters[$key] = (bool)$value;
				}
			}
		} else {
			$this->error = $this->_prepareFailedParameterResponse($validatedInput);
		}
	}

	/**
	 * Prepares an error for situations where the parameter validation fails.
	 *
	 * @param Zend_Filter_Input $validated_input
	 * @return Api3_EndpointError
	 */
	private function _prepareFailedParameterResponse($validated_input)
	{
		$endpointError = new Api3_EndpointError("failed_validation");

		if ($validated_input->hasInvalid())
		{
			$invalid = $validated_input->getInvalid();
			foreach ($invalid as $paramName => $paramArray)
			{
				foreach ($paramArray as $key => $value)
				{
					$error = new stdClass();
					$error->$key = $value;
					$endpointError->addError($paramName, $error);
				}
			}
		}
		if ($validated_input->hasMissing())
		{
			$missing = $validated_input->getMissing();
			foreach ($missing as $key => $value) {
				$error = new stdClass();
				$error->missing = $value;
				$endpointError->addError($key, $error);
			}
		}
		if ($validated_input->hasUnknown())
		{
			$unknown = $validated_input->getUnknown();
			foreach ($unknown as $key => $value) {
				//TODO: filter/escape things that need don't get validated
			}
		}
		return $endpointError;
	}

	/**
	 * Adds the validators to the request object.
	 *
	 * @param array $validatorsArray
	 */
	public function addValidators($validatorsArray)
	{
		$this->_validators = Api3_EndpointValidator::getValidators($validatorsArray);
	}

	/**
	 * Adds the filters to the request object.
	 *
	 * @param array $filtersArray
	 */
	public function addFilters($filtersArray)
	{
		$this->_filters = Api3_EndpointValidator::getFilters($filtersArray);
	}

	/**
	 * Returns whether the request object has any errors.
	 *
	 * @return boolean
	 */
	public function hasErrors()
	{
		if (isset($this->error))
		{
			return TRUE;
		}
	}

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