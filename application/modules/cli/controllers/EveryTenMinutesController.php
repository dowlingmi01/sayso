<?php
require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Cli_EveryTenMinutesController extends Api_GlobalController
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
		// Process survey responses that need it
		$this->view->messages = Survey_ResponseCollection::processAllResponsesPendingProcessing();

		// Process buckets
		ReportCellCollection::processAllReportCellConditions();
		$this->view->messages = array_merge($this->view->messages, array("Report Cell Processing Complete!"));

		quicklog(implode("\n", $this->view->messages));

		// End cli actions with this, otherwise you get a fatal error
		exit(0);
	}
}