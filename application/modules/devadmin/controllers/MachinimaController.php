<?php
require_once APPLICATION_PATH . '/modules/devadmin/controllers/IndexController.php';

class Devadmin_MachinimaController extends Devadmin_IndexController {
	protected $single_starbar_id = 4;

	public function preDispatch() {
		parent::preDispatch();

		$this->view->headTitle()->set("Recon Reports");
	}

	public function surveyReportAction () {
		$this->view->white_logo_url = "/images/machinima/Machinima_Co-Branded_Logo.png";
		$this->view->black_logo_url = "/images/machinima/Machinima_Report_Header.png";

		parent::surveyReportAction();

		$this->_helper->viewRenderer->setNoController();
		$this->_helper->viewRenderer->setScriptAction('index/survey-report');
	}
}

?>
