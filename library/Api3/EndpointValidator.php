<?php
/**
 * <p>Sets up the default structure and default values for the
 * EndpointValidator</p>
 *
 * @package Api3
 * @subpackage endpoint
 */
class Api3_EndpointValidator
{
	/**
	 * Stores all possible validation rules and returns an array of
	 * the rules requested by passing an array of the keys required.
	 *
	 * @param array $rules
	 * @return array
	 */
	public static function getValidators($rules)
	{
		//this array holds all of the possible validation rules
		//key is the field name to be validated,
		//value is the rule set in array format
		$validators = array(
						"int_required_notEmpty"		=> array(
													new Zend_Validate_Int(),
													'presence' => 'required'
													),
						"int"						=> array(
													new Zend_Validate_Int()
													),
						"required"					=> array(
													"presence" => "required"
													),
						"required_allowEmpty"		=> array(
													"allowEmpty" => TRUE,
													"presence" => "required"
													),
						"alpha_required_allowEmpty"	=> array(
													new Zend_Validate_Alpha(),
													"presence" => "required",
													"allowEmpty" => TRUE
													),
						"alpha_notEmpty"			=> array(
													new Zend_Validate_Alpha()
													),
						"alpha_required_notEmpty"		=> array(
													new Zend_Validate_Alpha(),
													"presence" => "required",
													),

						);
		$response = array();
		foreach ($rules as $key => $value)
		{
			if (array_key_exists($value, $validators))
			{
				$response[$key] = $validators[$value];
			}
		}
		return $response;
	}

	/**
	 * Stores all possible filter rules and returns an array of
	 * the rules requested by passing an array of the keys required
	 *
	 * @param array $rules
	 * @return array
	 */
	public static function getFilters($rules)
	{
		//this array holds all of the possible filter rules
		//key is the field name to be validated,
		//value is the rule set
		$filters = array(
						"bool"		=> new Zend_Filter_Boolean(Zend_Filter_Boolean::ALL)
					);

		$response = array();
		foreach ($rules as $key => $value)
		{
			if (array_key_exists($value, $filters))
			{
				$response[$key] = $filters[$value];
			}
		}
		return $response;
	}
}