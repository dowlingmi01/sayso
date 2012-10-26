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

		if (!$surveyQuestion->id || !$surveyResponse->id || $surveyResponse->status == "completed" || $surveyResponse->status == "disqualified") return $this->_resultType(false);

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
		$surveyResponse->processing_status = "completed";
		$surveyResponse->data_download = new Zend_Db_Expr('now()');
		$surveyResponse->completed_disqualified = new Zend_Db_Expr('now()');
		$surveyResponse->save();

		// reward the user
		Game_Starbar::getInstance()->completeSurvey($survey);

		// success
		return $this->_resultType(true);
	}


	public function userQuizSubmitAction () {
		$this->_validateRequiredParameters(array('survey_id', 'user_id', 'user_key', 'survey_question_choice_id'));

		$survey = new Survey();
		$survey->loadData($this->survey_id);
		if (!$survey->id) return $this->_resultType(false);

		// A quiz has only one question... load it
		$surveyQuestion = new Survey_Question();
		$surveyQuestion->loadDataByUniqueFields(array("survey_id" => $this->survey_id));

		$surveyResponse = new Survey_Response();
		$surveyResponse->loadDataByUniqueFields(array("survey_id" => $this->survey_id, "user_id" => $this->user_id));

		if (!$surveyQuestion->id || !$surveyResponse->id || $surveyResponse->status == "completed" || $surveyResponse->status == "disqualified") return $this->_resultType(false);

		// Find the user's choice
		$surveyQuestionChoice = new Survey_QuestionChoice();
		$surveyQuestionChoice->loadData($this->survey_question_choice_id);

		if (!$surveyQuestionChoice->id || $surveyQuestionChoice->survey_question_id != $surveyQuestion->id) return $this->_resultType(false);

		$surveyQuestionResponse = new Survey_QuestionResponse();
		$surveyQuestionResponse->survey_response_id = $surveyResponse->id;
		$surveyQuestionResponse->survey_question_id = $surveyQuestion->id;
		$surveyQuestionResponse->survey_question_choice_id = $surveyQuestionChoice->id;
		$surveyQuestionResponse->data_type = "choice";
		$surveyQuestionResponse->save();

		$surveyResponse->status = "completed";
		$surveyResponse->processing_status = "completed";
		$surveyResponse->data_download = new Zend_Db_Expr('now()');
		$surveyResponse->completed_disqualified = new Zend_Db_Expr('now()');
		$surveyResponse->save();

		if ($surveyQuestionChoice->correct) {
			$correctSurveyQuestionChoiceId = $surveyQuestionChoice->id;
			// reward the user
			Game_Starbar::getInstance()->completeSurvey($survey);
		} else {
			$correctSurveyQuestionChoiceId = Survey_QuestionChoice::getCorrectChoiceIdForQuestion($surveyQuestion->id);
		}

		return $this->_resultType(new Object(array("correct_survey_question_choice_id" => $correctSurveyQuestionChoiceId)));
	}

	public function userTrailerSubmitAction() {
		$this->_validateRequiredParameters(array('user_id', 'user_key', 'starbar_id', 'survey_id', 'first_choice_id', 'second_choice_id'));

		$survey = new Survey();
		$survey->loadData($this->survey_id);
		if (!$survey->id) return $this->_resultType(false);

		$surveyResponse = new Survey_Response();
		$surveyResponse->loadDataByUniqueFields(array("survey_id" => $this->survey_id, "user_id" => $this->user_id));

		if (!$surveyResponse->id || $surveyResponse->status == 'completed' || $surveyResponse->status == "disqualified") return $this->_resultType(false);

		// Delete any existing responses (in case of previous partial response, for whatever reason)
		$surveyResponse->deleteQuestionResponses();

		$surveyQuestions = new Survey_QuestionCollection();
		$surveyQuestions->loadAllQuestionsForSurvey($survey->id);

		foreach ($surveyQuestions as $surveyQuestion) {
			$choiceId = ($surveyQuestion->ordinal == 1 ? $this->first_choice_id : $this->second_choice_id);
			// Verify the choice is valid
			$surveyQuestionChoice = new Survey_QuestionChoice();
			$surveyQuestionChoice->loadDataByUniqueFields(array('id' => $choiceId, 'survey_question_id' => $surveyQuestion->id));

			if (!$surveyQuestionChoice->id) return $this->_resultType(false);

			$surveyQuestionResponse = new Survey_QuestionResponse();
			$surveyQuestionResponse->survey_response_id = $surveyResponse->id;
			$surveyQuestionResponse->survey_question_id = $surveyQuestion->id;
			$surveyQuestionResponse->survey_question_choice_id = $surveyQuestionChoice->id;
			$surveyQuestionResponse->data_type = "choice";
			$surveyQuestionResponse->save();
		}

		$surveyResponse->status = "completed";
		$surveyResponse->processing_status = "completed";
		$surveyResponse->data_download = new Zend_Db_Expr('now()');
		$surveyResponse->completed_disqualified = new Zend_Db_Expr('now()');
		$surveyResponse->save();

		Game_Starbar::getInstance()->completeSurvey($survey);

		return $this->_resultType(true);
	}
	public function userMissionSubmitAction() {
		$this->_validateRequiredParameters(array('user_id', 'user_key', 'starbar_id', 'top_frame_id', 'mission_short_name', 'mission_data'));
		
		$missionInfo = new Survey_MissionInfo();
		$missionInfo->loadDataByUniqueFields(array('short_name'=>$this->mission_short_name));
		if( !$missionInfo->id )
			return $this->_resultType(false);
		
		$surveyResponse = new Survey_Response();
		$surveyResponse->loadDataByUniqueFields(array("survey_id" => $missionInfo->survey_id, "user_id" => $this->user_id));

		if (!$surveyResponse->id || $surveyResponse->status == 'completed' || $surveyResponse->status == "disqualified")
			return $this->_resultType(false);
		
		$missionProgress = new Survey_MissionProgress();
		$missionProgress->survey_id = $missionInfo->survey_id;
		$missionProgress->user_id = $this->user_id;
		$missionProgress->top_frame_id = $this->top_frame_id;
		$missionProgress->stage = $this->mission_data['stage'];
		$missionProgress->save();
			
        if( $this->mission_data['stage'] == $missionInfo->number_of_stages ) {
        	// TODO Validate and save answers
        	
			$surveyResponse->status = 'completed';
 			$surveyResponse->processing_status = 'completed';
 			$surveyResponse->data_download = new Zend_Db_Expr('now()');
			$surveyResponse->completed_disqualified = new Zend_Db_Expr('now()');
			$surveyResponse->save();
			
			$survey = new Survey();
			$survey->loadData($missionInfo->survey_id);
			
			Game_Starbar::getInstance()->completeSurvey($survey);
		}
		return $this->_resultType(true);
	}

}