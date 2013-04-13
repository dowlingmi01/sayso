<?php

class Api_GlobalController extends Api_AbstractController
{
	protected function _setIntervals() {
		$intervals = array();
		$intervals['notifications'] = (int) Api_Registry::getConfig()->interval->notifications;
		$intervals['studies'] = (int) Api_Registry::getConfig()->interval->studies;
		$this->_request->setParam(Api_AbstractController::INTERVALS, $intervals);
	}
	public function postDispatch()
	{
		if( Game_Transaction::wasTransactionExecuted() && $this->user_id && $this->starbar_id )
			Game_Transaction::addGameToRequest($this->getRequest());
	}
}