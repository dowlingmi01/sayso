<?php
/**
 * <p>Sets up the default structure and default values for the
 * EndpointError</p>
 *
 * @package Ssmart
 * @subpackage endpoint
 */
class Ssmart_EndpointError
{
	/**
	 *Instantiates the meta object.
	 *
	 * <p>This is where all of the meta data for an error is written.
	 * It is deleted before the error is passed back to the api.</p>
	 *
	 * @var \stdClass
	 */
	public $meta;

	public $errors;

	/**
	 * Constructor
	 *
	 * <p>If <code>@errorName</code> is defined, write it to
	 * the object.</p>
	 *
	 * @param string $errorName The name of the error being created.
	 */
	public function __construct($errorName = NULL) {
		$this->meta = new Object();
		$this->errors = new Object();

		if ($errorName)
		{
			$this->meta->error_name = $errorName;
		}
	}

	/**
	 * Writes a predefined error to the object.
	 *
	 * @param mixed $value
	 * @param string $name
	 */
	public function addError($value, $name = NULL)
	{
		if (!$name)
		{
			$this->errors = $value;
		} else {
			$this->errors->$name = $value;
		}
	}
}