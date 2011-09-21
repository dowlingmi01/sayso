<?php

require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Api_SurveyController extends Api_GlobalController
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
    }
    
    public function surveyGizmoSubmitAction ()
    {
        // dumping the post data into the log (remove this after testing)
        $logger = new Zend_Log();
        $logger->addWriter(new Zend_Log_Writer_Stream(LOG_PATH . '/api.log'));
        $logger->log(print_r($this->_request->getPost(), true), Zend_Log::INFO);

		$request = $this->getRequest();
		$surveyId = (int) $request->getParam('survey_id');
		$userId = (int) $request->getParam('user_id');

		$this->_markSurveyCompletedByUser($surveyId, $userId);

        // success
        return $this->_resultType(true);
    }

    public function userPollSubmitAction () {
        // dumping the post data into the log (remove this after testing)
        $logger = new Zend_Log();
        $logger->addWriter(new Zend_Log_Writer_Stream(LOG_PATH . '/api.log'));
        $logger->log(print_r($this->_request->getPost(), true), Zend_Log::INFO);

		$request = $this->getRequest();
		$surveyId = (int) $request->getParam('survey_id');
		// authentication is done via user_id and user_key sent by ajax request

		$this->_markSurveyCompletedByUser($surveyId, $this->user_id);

        // success
        return $this->_resultType(true);
    }

    private function _markSurveyCompletedByUser ($surveyId, $userId) {
    	// load the survey (i.e. check if it exists);
    	$survey = new Survey();
		$survey->loadData($surveyId);

    	// @todo this should also check if the user has access to the starbar/survey
        $results = Db_Pdo::fetch("SELECT survey_id FROM survey_user_map WHERE survey_id = ? AND user_id = ? AND status = 'complete'", $survey->id, $userId);
        if ($results) {
        	return false; // user has already completed this survey
		} else {
			$surveyUserMap = new Survey_UserMap();
			$surveyUserMap->survey_id = $survey->id;
			$surveyUserMap->user_id = $userId;
			$surveyUserMap->status = "complete";

			if ($surveyUserMap->save()) {
				// todo award points here
				return true;
			}
		}
	}
		

    
}

