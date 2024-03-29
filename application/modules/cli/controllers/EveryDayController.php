<?php
require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Cli_EveryDayController extends Api_GlobalController
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
		$sql = "SELECT *
				FROM starbar
				WHERE id > 2
				";
		$starbars = Db_Pdo::fetchAll($sql);

		foreach ($starbars as $starbar) {
			$starbarId = $starbar['id'];
			$summary = SummaryReportHack::getReportResults($starbarId);
			$cache = Api_Cache::getInstance('summary_reports_'.$starbarId, Api_Cache::LIFETIME_WEEK);
			$cache->save($summary);
		}

		// End cli actions with this, otherwise you get a fatal error
		exit(0);
	}
}