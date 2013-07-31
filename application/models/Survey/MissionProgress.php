<?php

class Survey_MissionProgress extends Record
{
	protected $_tableName = 'survey_mission_progress';
	protected $_uniqueFields = array('survey_id' => 0, 'user_id' => 0, 'top_frame_id' => 0);

	public static function update($user_id, $starbar_id, $top_frame_id, $mission_short_name, $mission_data) {
		$missionInfo = new Survey_MissionInfo();
		$missionInfo->loadDataByUniqueFields(array('short_name'=>$mission_short_name));
		if( !$missionInfo->id )
			return false;

		$surveyResponse = new Survey_Response();
		$surveyResponse->loadDataByUniqueFields(array("survey_id" => $missionInfo->survey_id, "user_id" => $user_id));

		if (!$surveyResponse->id || $surveyResponse->status == 'completed' || $surveyResponse->status == "disqualified")
			return false;

		$missionProgress = new Survey_MissionProgress();
		$missionProgress->survey_id = $missionInfo->survey_id;
		$missionProgress->user_id = $user_id;
		$missionProgress->top_frame_id = $top_frame_id;
		$missionProgress->stage = $mission_data->stage;
		$missionProgress->save();

		if( $mission_data->stage == $missionInfo->number_of_stages ) {
			$fileLocation = realpath(APPLICATION_PATH . '/../public/client/missions/mission/' . $mission_short_name);
			$filePath = $fileLocation . '/model.json';
			$fileContents = file_get_contents($filePath);
			$missionData = Zend_Json::decode($fileContents);
			$answerStages = $mission_data->data->stages;
			$answers = array();
			foreach( $missionData['stages'] as $stageNum => $stage ) {
				if( array_key_exists('question', $stage['data']) )
					self::_verifyMissionAnswer( $stage['data'], $answerStages->{$stageNum}->data, $answers );
				else
					foreach($stage['data']['questions'] as $questNum => $question)
						self::_verifyMissionAnswer( $question, $answerStages->{$stageNum}->data->questions->{$questNum}, $answers );
			}
			foreach( $answers as $answer ) {
				$surveyQuestionResponse = new Survey_QuestionResponse();
				$surveyQuestionResponse->data_type = 'choice';
				$surveyQuestionResponse->survey_response_id = $surveyResponse->id;
				$surveyQuestionResponse->survey_question_id = $answer['question_id'];
				$surveyQuestionResponse->survey_question_choice_id = $answer['answer_id'];
				$surveyQuestionResponse->save();
			}

			$surveyResponse->status = 'completed';
			$surveyResponse->processing_status = 'completed';
			$surveyResponse->data_download = new Zend_Db_Expr('now()');
			$surveyResponse->completed_disqualified = new Zend_Db_Expr('now()');
			$surveyResponse->save();

			$survey = new Survey();
			$survey->loadData($missionInfo->survey_id);

			Game_Transaction::completeSurvey($user_id, $starbar_id, $survey);
		}
		return true;
	}

	private static function _verifyMissionAnswer( $questionDef, $userAns, &$answers ) {
		$answerId = $userAns->selectedAnswerId;
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
