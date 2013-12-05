<?php
require_once APPLICATION_PATH . '/modules/devadmin/controllers/IndexController.php';

class Devadmin_MachinimareloadController extends Devadmin_IndexController {
	protected $single_starbar_id = 7;

	public function preDispatch() {
		parent::preDispatch();

		$this->view->white_logo_url = "/images/machinimareload/Machinima_Co-Branded_Logo.png";
		$this->view->black_logo_url = "/images/machinimareload/Machinima_Report_Header.png";

		$this->view->headTitle()->set("Reload Reports");

		$this->_helper->viewRenderer->setNoController();
		$this->_helper->viewRenderer->setScriptAction('index/'.$this->getRequest()->getActionName());
	}
}

?>
