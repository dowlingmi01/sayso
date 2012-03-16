<?php

class Survey_QuestionChoiceCollection extends RecordCollection
{
	public function loadAllQuestionChoicesForSurvey ($surveyId) {
		if ($surveyId) {
			$sql = "
				SELECT survey_question_choice.*, survey_question.ordinal AS questionordinal
				FROM survey_question_choice, survey_question
				WHERE survey_question_choice.survey_question_id = survey_question.id
					AND survey_question.survey_id = ?
				ORDER BY questionordinal, survey_question_choice.ordinal ASC";

			$data = Db_Pdo::fetchAll($sql, $surveyId);

			if ($data) {
				$this->build($data, new Survey_QuestionChoice());
			}
		}
	}

	public function loadAllQuestionChoicesForSurveyQuestion($surveyQuestionId) {
		if ($surveyQuestionId) {
			$surveyQuestion = new Survey_Question();
			$surveyQuestion->loadData($surveyQuestionId);

			if (!$surveyQuestion->id) return;

			// If it's a piped question, the options come from the parent question
			if ($surveyQuestion->piped_from_survey_question_id) $surveyQuestionId = $surveyQuestion->piped_from_survey_question_id;

			$sql = "
				SELECT *
				FROM survey_question_choice
				WHERE survey_question_id = ?
				ORDER BY ordinal ASC";

			$data = Db_Pdo::fetchAll($sql, $surveyQuestionId);

			if ($data) {
				$this->build($data, new Survey_QuestionChoice());
			}
		}
	}
}
