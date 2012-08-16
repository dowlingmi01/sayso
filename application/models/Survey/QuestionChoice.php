<?php

class Survey_QuestionChoice extends Record
{
	protected $_tableName = 'survey_question_choice';

	public function getStringOfUsersWhoChoseThisChoice($surveyQuestionId, $commaDelimitedUserIdFilterList = null) {
		if (!$this->id) return;
		if (!$surveyQuestionId) return;

		$sql = "
			SELECT GROUP_CONCAT(DISTINCT sr.user_id) AS user_id_list
			FROM survey_response sr, survey_question_response sqr
			WHERE sqr.survey_response_id = sr.id
				AND sr.processing_status = 'completed'
				AND sqr.survey_question_id = ?
				AND sqr.survey_question_choice_id = ?
		";

		if ($commaDelimitedUserIdFilterList) {
			// add to $sql
			$sql .= " AND sr.user_id IN (" . trimCommas($commaDelimitedUserIdFilterList) . ")";
		}

		$result = Db_Pdo::fetch($sql, $surveyQuestionId, $this->id);
		return $result['user_id_list'];
	}

	public function getArrayOfQuestionsThatShareThisChoice() {
		if (!$this->id) return;

		$sql = "
			SELECT survey_question_id
			FROM survey_question_response
			WHERE survey_question_choice_id = ?
			GROUP BY survey_question_id
		";

		return Db_Pdo::fetchColumn($sql, $this->id);
	}

	public static function getCorrectChoiceIdForQuestion($surveyQuestionId) {
		$sql = "
			SELECT id
			FROM survey_question_choice
			WHERE survey_question_id = ?
				AND correct IS true
		";
		$result = Db_Pdo::fetch($sql, $surveyQuestionId);
		$correctSurveyQuestionChoiceId = (int) $result['id'];

		return $correctSurveyQuestionChoiceId;
	}

	public static function getNumberOfResponsesForChoice($surveyQuestionChoiceId) {
		$sql = "
			SELECT count(id) AS theCount
			FROM survey_question_response
			WHERE survey_question_choice_id = ?
		";
		$result = Db_Pdo::fetch($sql, $surveyQuestionChoiceId);
		$numberOfResponses = (int) $result['theCount'];

		return $numberOfResponses;
	}
}
