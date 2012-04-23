<?php
require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Cli_EveryHourController extends Api_GlobalController
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
		// @todo process report cells here

		// End cli actions with this, otherwise you get a fatal error
		exit(0);
	}
}