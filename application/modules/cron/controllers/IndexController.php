<?php
/**
 * Actions in this controller are for cron jobs to call
 */
require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Cron_IndexController extends Api_GlobalController
{
	public function preDispatch() {

	}

	public function indexAction () {

	}

	public function onceEveryFiveMinutesAction () {
		// Process survey responses that need it
		Survey_ResponseCollection::processAllResponsesPendingProcessing();
	}
}
