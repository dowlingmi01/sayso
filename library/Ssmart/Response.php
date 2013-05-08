<?php
/**
 * <p>Sets up the default structure and default values for the
 * response object for passing around the api.</p>
 *
 * @package Ssmart
 */
class Ssmart_Response
{
	/**
	 * Sets default error code to 0 so no errors are triggered unless
	 * explicitly set.
	 *
	 * @var int
	 */
	public $error_code = 0;

	/**
	 * Sets the default error_message to an empty string.
	 *  This clears out preexisting errors when the response is reset
	 * after a call has been processed.
	 *
	 * @var string
	 */
	public $error_message = "";

	/**
	 * Constructs the response object.
	 *
	 * @param Ssmart_Request $data The processed and formated
	 *	request object
	 * @param Ssmart_Error $error The error object
	 */
	public function __construct($data = NULL) {
		if ($data && isset($data->requests))
		{
			//prepare request response
			foreach ($data->requests as $key=>$value)
			{
				$this->responses->$key = '';
			}
		}
	}
}