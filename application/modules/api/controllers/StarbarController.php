<?php

require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Api_StarbarController extends Api_GlobalController
{

	public function init()
	{

	}

	public function indexAction()
	{
	}

	public function setOnboardStatusAction () {
		$this->_validateRequiredParameters(array('starbar_id', 'user_id', 'user_key', 'status'));
		$starbarUserMap = new Starbar_UserMap();
		$starbarUserMap->loadDataByUniqueFields(array('starbar_id' => (int) $this->starbar_id, 'user_id' => (int) $this->user_id));
		$starbarUserMap->onboarded = (int) $this->status;
		$starbarUserMap->save();
		return $this->_resultType($starbarUserMap);
	}

	public function addAction() {
		$this->_validateRequiredParameters(array('new_starbar_id', 'user_id'));

		$userState = new User_State();
		$userState->loadDataByUniqueFields(array('user_id' => $this->user_id));

		$starbar = new Starbar();
		$starbar->loadData($this->new_starbar_id);

        $userState->addStarbar($starbar, $this->_request);

		return $this->_resultType($userState);
	}
}


