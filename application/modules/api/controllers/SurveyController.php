<?php

require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Api_SurveyController extends Api_GlobalController
{
	public function init()
	{
		/* Initialize action controller here */
	}

	public function userPollSubmitAction () {
		$this->_validateRequiredParameters(array('survey_id', 'user_id', 'user_key', 'external_choice_id'));

		$survey = new Survey();
		$survey->loadData($this->survey_id);
		if (!$survey->id) return $this->_resultType(false);

		// A poll has only one question... load it
		$surveyQuestion = new Survey_Question();
		$surveyQuestion->loadDataByUniqueFields(array("survey_id" => $this->survey_id));

		$surveyResponse = new Survey_Response();
		$surveyResponse->loadDataByUniqueFields(array("survey_id" => $this->survey_id, "user_id" => $this->user_id));

		if (!$surveyQuestion->id || !$surveyResponse->id) return $this->_resultType(false);

		// Find the user's choice
		$surveyQuestionChoice = new Survey_QuestionChoice();
		$surveyQuestionChoice->loadDataByUniqueFields(array("survey_question_id" => $surveyQuestion->id, "external_choice_id" => $this->external_choice_id));

		if (!$surveyQuestionChoice->id) return $this->_resultType(false);

		$surveyQuestionResponse = new Survey_QuestionResponse();
		$surveyQuestionResponse->survey_response_id = $surveyResponse->id;
		$surveyQuestionResponse->survey_question_id = $surveyQuestion->id;
		$surveyQuestionResponse->survey_question_choice_id = $surveyQuestionChoice->id;
		$surveyQuestionResponse->data_type = "choice";
		$surveyQuestionResponse->save();

		$surveyResponse->status = "completed";
		$surveyResponse->process_status = "completed";
		$surveyResponse->data_download = new Zend_Db_Expr('now()');
		$surveyResponse->completed_disqualified = new Zend_Db_Expr('now()');
		$surveyResponse->save();

		// reward the user
		Game_Starbar::getInstance()->completeSurvey($survey);

		// success
		return $this->_resultType(true);
	}
}
