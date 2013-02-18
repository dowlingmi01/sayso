<?php
require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Cli_ImportBDEconomyController extends Api_GlobalController
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
		$economy_id = $_SERVER['argv'][2];
		
		Game_Transaction::importBDEconomy($economy_id);
		exit(0);
	}
}