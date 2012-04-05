<?php

class Survey_QuestionChoice extends Record
{
	protected $_tableName = 'survey_question_choice';

	public function getArrayOfUsersWhoChoseThisChoice($surveyQuestionId, $commaDelimitedUserIdFilterList = null) {
		if (!$this->id) return;
		if (!$surveyQuestionId) return;

		$sql = "
			SELECT sr.user_id
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

		return Db_Pdo::fetchColumn($sql, $surveyQuestionId, $this->id);
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
}
