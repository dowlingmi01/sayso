<?php
/**
 * <p>Sets up the default structure and default values for the
 * EndpointError</p>
 *
 * @package Api3
 * @subpackage endpoint
 */
class Api3_EndpointError
{
	/**
	 *Instansiates the meta object.
	 *
	 * <p>This is where all of the meta data for an error is written.
	 * It is deleted before the error is passed back to the api.</p>
	 *
	 * @var \stdClass
	 */
	public $meta;

	/**
	 * Construstor
	 *
	 * <p>If <code>@errorName</code> is defined, wirte it to
	 * the object.</p>
	 *
	 * @param string $errorName The name of the error being created.
	 */
	public function __construct($errorName = NULL) {
		if ($errorName)
		{
			$this->meta->errorName = $errorName;
		}
	}

	/**
	 * Writes a predefined error to the object.
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function addError($name, $value)
	{
		$this->errors->$name = $value;
	}
}