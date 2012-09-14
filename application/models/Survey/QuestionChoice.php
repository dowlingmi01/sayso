<?php

class Survey_QuestionChoice extends Record
{
	protected $_tableName = 'survey_question_choice';

	public function getCountOfUsersInReportCellWhoChoseThisChoice($surveyQuestionId, $reportCellId = 1) {
		if (!$this->id) return;
		if (!$surveyQuestionId) return;

		$joinClause;
		if ($reportCellId > 1) {
			$joinClause = " INNER JOIN report_cell_user_map rcum ON rcum.user_id = sr.user_id AND rcum.report_cell_id = ". $reportCellId . " ";
		} else { // filter out test users
			$joinClause = " INNER JOIN user u ON sr.user_id = u.id AND u.type != 'test' ";
		}

		$sql = "
			SELECT COUNT(sr.user_id) AS number_of_users
			FROM survey_response sr
			INNER JOIN survey_question_response sqr
				ON sqr.survey_response_id = sr.id
			".$joinClause."
			WHERE sr.processing_status = 'completed'
				AND sqr.survey_question_id = ?
				AND sqr.survey_question_choice_id = ?
		";

		$data = Db_Pdo::fetch($sql, $surveyQuestionId, $this->id);

		if ($data) return (int) $data['number_of_users'];
		return 0;
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
