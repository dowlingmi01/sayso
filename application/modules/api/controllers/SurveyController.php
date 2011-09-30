<?php

require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Api_SurveyController extends Api_GlobalController
{
	public function preDispatch()
	{
		$request = $this->getRequest();
		/*
		* bundle_of_joy is the variable sent to and from SurveyGizmo.
		* It is a 'manually' serialized list of variables and values, where ^|^ seperates variables
		* from each other, and ^-^ seperates variable names from variable values. Example:
		* GET version: ?user_id=1&user_key=123&auth_key=abc
		* bundle_of_joy version: ?bundle_of_joy=user_id^-^1^|^user_key^-^123^|^auth_key^-^abc
		*/
        if ($request->getParam('bundle_of_joy')) {
            foreach (explode('^|^', $request->getParam('bundle_of_joy')) as $keyValue) {
                $parts = explode('^-^', $keyValue);
                $request->setParam($parts[0], $parts[1]);
            }
        }
	}   

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
    }
    
    public function surveyGizmoSubmitAction ()
    {
		$request = $this->getRequest();
		$surveyId = (int) $request->getParam('survey_id');
		$userId = (int) $request->getParam('user_id');

		$this->_markSurveyCompletedByUser($surveyId, $userId);

        // success
        return $this->_resultType(true);
    }

    public function surveyGizmoDisqualifyAction ()
    {
		$request = $this->getRequest();
		$surveyId = (int) $request->getParam('survey_id');
		$userId = (int) $request->getParam('user_id');

		$this->_markSurveyUserDisqualified($surveyId, $userId);

        // success
        return $this->_resultType(true);
    }

    public function userPollSubmitAction () {
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
	        $results = Db_Pdo::fetch("SELECT survey_id FROM survey_user_map WHERE survey_id = ? AND user_id = ?", $survey->id, $userId);
	        if ($results) { // user already has map to survey, update status to complete
        		Db_Pdo::execute("UPDATE survey_user_map SET status = 'complete' WHERE survey_id = ? AND user_id = ?", $survey->id, $userId);
			} else { // create 
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
		

    private function _markSurveyUserDisqualified ($surveyId, $userId) {
    	// @todo mark disqualified users differently?
    	$this->_markSurveyCompletedByUser($surveyId, $userId);
	}
}

