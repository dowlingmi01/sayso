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
		$this->_validateRequiredParameters(array('survey_id', 'user_id', 'user_key'));
		$result = $this->_updateSurveyResponseStatus($this->survey_id, $this->user_id, 'completed');

		// success
		return $this->_resultType($result);
	}

	public function surveyGizmoDisqualifyAction ()
	{
		$this->_validateRequiredParameters(array('survey_id', 'user_id', 'user_key'));
		$result = $this->_updateSurveyResponseStatus($this->survey_id, $this->user_id, 'disqualified');

		// success
		return $this->_resultType($result);
	}

	public function userPollSubmitAction () {
		$this->_validateRequiredParameters(array('survey_id', 'user_id', 'user_key'));
		$result = $this->_updateSurveyResponseStatus($this->survey_id, $this->user_id, 'completed');

		// success
		return $this->_resultType($result);
	}

	private function _updateSurveyResponseStatus ($surveyId, $userId, $newStatus, $externalResponseId = -1) {
		// load the survey (i.e. check if it exists);
		$survey = new Survey();
		$survey->loadData($surveyId);

		// @todo this should also check if the user has access to the starbar/survey
		$results = Db_Pdo::fetch("SELECT survey_id FROM survey_response WHERE survey_id = ? AND user_id = ? AND (status = 'completed' OR status = 'disqualified')", $survey->id, $userId);
		if ($results) {
			return false; // user has already completed this survey
		} else {

			$surveyResponse = new Survey_Response();
			$surveyResponse->loadDataByUniqueFields(array('survey_id' => $survey->id, 'user_id' => $userId));

			// Response exists, update the status and the external_response_id if $externalResponseId is set
			if ($surveyResponse->id) {
				$surveyResponse->status = $newStatus;

				$externalResponseId = (int) $externalResponseId;
				if ($externalResponseId != -1) {
					$surveyResponse->external_response_id = $externalResponseId;
				}
			} else { // Create the response
				$surveyResponse->survey_id = $survey->id;
				$surveyResponse->user_id = $userId;
				$surveyResponse->status = $newStatus;
				$surveyResponse->external_response_id = (int) $externalResponseId;
			}

			if ( ((int)$surveyResponse->external_response_id) < 1 ) $surveyResponse->external_response_id = null;

			$surveyResponse->save();

			// reward the user
			if ($newStatus == "completed") {
				Game_Starbar::getInstance()->completeSurvey($survey);
			} elseif ($newStatus == "disqualified") {
				Game_Starbar::getInstance()->disqualifySurvey($survey);
			}
			return true;
		}
	}
}
