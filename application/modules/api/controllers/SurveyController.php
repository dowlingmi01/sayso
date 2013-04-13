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
		Game_Transaction::completeSurvey($this->user_id, $this->starbar_id, $survey);

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
			Game_Transaction::completeSurvey($this->user_id, $this->starbar_id, $survey);
		} else {
			$correctSurveyQuestionChoiceId = Survey_QuestionChoice::getCorrectChoiceIdForQuestion($surveyQuestion->id);
		}

		return $this->_resultType(new Object(array("correct_survey_question_choice_id" => $correctSurveyQuestionChoiceId)));
	}

	/**
	* Runs after the user has completed the two trailer questions
	*
	*/
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

		//PTC it came from here

		Game_Transaction::completeSurvey($this->user_id, $this->starbar_id, $survey);

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
        	try {
				$fileLocation = realpath(APPLICATION_PATH . '/../public/client/missions/mission/' . $this->mission_short_name);
				$filePath = $fileLocation . '/model.json';
				$fileContents = file_get_contents($filePath);
				$missionData = Zend_Json::decode($fileContents);
				$answerStages = $this->mission_data['data']['stages'];
				$answers = array();
				foreach( $missionData['stages'] as $stageNum => $stage ) {
					if( array_key_exists('question', $stage['data']) )
						$this->_verifyMissionAnswer( $stage['data'], $answerStages[$stageNum]['data'], $answers );
					else
						foreach($stage['data']['questions'] as $questNum => $question)
							$this->_verifyMissionAnswer( $question, $answerStages[$stageNum]['data']['questions'][$questNum], $answers );
				}
				foreach( $answers as $answer ) {
					$surveyQuestionResponse = new Survey_QuestionResponse();
					$surveyQuestionResponse->data_type = 'choice';
					$surveyQuestionResponse->survey_response_id = $surveyResponse->id;
					$surveyQuestionResponse->survey_question_id = $answer['question_id'];
					$surveyQuestionResponse->survey_question_choice_id = $answer['answer_id'];
					$surveyQuestionResponse->save();
				}
			} catch( Exception $e ) {
				return $this->_resultType(false);
			}

			$surveyResponse->status = 'completed';
 			$surveyResponse->processing_status = 'completed';
 			$surveyResponse->data_download = new Zend_Db_Expr('now()');
			$surveyResponse->completed_disqualified = new Zend_Db_Expr('now()');
			$surveyResponse->save();

			$survey = new Survey();
			$survey->loadData($missionInfo->survey_id);

			Game_Transaction::completeSurvey($this->user_id, $this->starbar_id, $survey);
		}
		return $this->_resultType(true);
	}

	/**
	* Called from Ajax (trailer.phtml) when a trailer has been viewed by the user.
	* If there is a related_survey_id, a check is done to see if the user is permitted
	* to see that survey, and if so, it is inserted as a link
	*
	*/
	public function markSurveyNewForUserAction()	{

		$this->_validateRequiredParameters(array('user_id', 'user_key', 'starbar_id'));

		if ($this->related_survey_id!=null) {
			// We have a related survey.

			// Check if this user has already seen this survey
			if (Survey_Response::checkIfUserHasCompletedSurvey($this->user_id,$this->related_survey_id)) {
				return false;
			} else {
				// User has not seen this survey. Add it to their list
				Survey_Response::addSurveyForUser($this->user_id,$this->related_survey_id);
				return true;
			}
		} else {
			return false; // There is no related survey for this trailer
		}
	}

	private function _verifyMissionAnswer( $questionDef, $userAns, &$answers ) {
		$answerId = $userAns['selectedAnswerId'];
		if( !$answerId )
			throw new Exception('Invalid data.');
		foreach( $questionDef['answers'] as $answer )
			if( $answer['id'] == $answerId ) {
				$answers[] = array( 'question_id' => $questionDef['question']['id'], 'answer_id'=>$answerId);
				return;
			}
		throw new Exception('Invalid data.');
	}
}