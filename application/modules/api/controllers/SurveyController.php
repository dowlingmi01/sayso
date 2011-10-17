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
        $this->_validateRequiredParameters(array('survey_id', 'user_id'));
		$result = $this->_updateSurveyUserMapStatus($this->survey_id, $this->user_id, 'complete');

        // success
        return $this->_resultType($result);
    }

    public function surveyGizmoDisqualifyAction ()
    {
        $this->_validateRequiredParameters(array('survey_id', 'user_id'));
		// @todo $this->_updateSurveyUserMapStatus($this->survey_id, $this->user_id, 'disqualified');
		$result = $this->_updateSurveyUserMapStatus($this->survey_id, $this->user_id, 'complete');

        // success
        return $this->_resultType($result);
    }

    public function userPollSubmitAction () {
        $this->_validateRequiredParameters(array('survey_id', 'user_id'));
		$result = $this->_updateSurveyUserMapStatus($this->survey_id, $this->user_id, 'complete');

        // success
        return $this->_resultType($result);
    }

    private function _updateSurveyUserMapStatus ($surveyId, $userId, $newStatus) {
    	// load the survey (i.e. check if it exists);
    	$survey = new Survey();
		$survey->loadData($surveyId);

    	// @todo this should also check if the user has access to the starbar/survey
        $results = Db_Pdo::fetch("SELECT survey_id FROM survey_user_map WHERE survey_id = ? AND user_id = ? AND (status = 'complete' OR status = 'disqualified')", $survey->id, $userId);
        if ($results) {
			return false; // user has already completed this survey
		} else {
	        $results = Db_Pdo::fetch("SELECT survey_id FROM survey_user_map WHERE survey_id = ? AND user_id = ?", $survey->id, $userId);
	        if ($results) { // user already has map to survey, update status to complete
        		Db_Pdo::execute("UPDATE survey_user_map SET status = ? WHERE survey_id = ? AND user_id = ?", $newStatus, $survey->id, $userId);
			} else { // create 
				$surveyUserMap = new Survey_UserMap();
				$surveyUserMap->survey_id = $survey->id;
				$surveyUserMap->user_id = $userId;
				$surveyUserMap->status = $newStatus;
				$surveyUserMap->save();
			}

			// award the user
			if ($newStatus == "complete") {
				Game_Starbar::getInstance()->completeSurvey($survey);
			/* @todo } elseif ($newStatus == "disqualified") {
				Game_Starbar::getInstance()->disqualifySurvey($survey);*/
			}
			return true;
		}
	}
}
