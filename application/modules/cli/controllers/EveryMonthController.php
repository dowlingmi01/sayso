<?php
require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Cli_EveryMonthController extends Api_GlobalController
{

	public function init()
	{
		if (PHP_SAPI != 'cli')
		{
			throw new Exception("Unsupported call!");
		}
	}

	/**
	 * All function calls should go here
	 */
	public function runAction()
	{
		// Insert functions to be run once a month here

		// End cli actions with this, otherwise you get a fatal error
		exit(0);
	}
}