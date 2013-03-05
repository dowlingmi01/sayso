<?php
/**
 * sets up the default structure and default values for the
 * response object for passing around the api
 */
class Api3_Response
{
	/**sets default error code to 0 so no errors are triggered unless
	 * explicitly set
	 *
	 * @var int
	 */
	public $error_code = 0;

	/**sets the default error_message to an empty string
	 * this clears out preexisting errors when the response is reset
	 * after a call has been processed
	 *
	 * @var string
	 */
	public $error_message = "";

	/**constructs the response object
	 *
	 * @param Api3_Request $data - the processed and formated
	 *	request object
	 * @param Api3_Error $error - the error object
	 */
	public function __construct($data = NULL, Api3_ApiError $error = NULL) {
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