<?php

class Survey_QuestionCollection extends RecordCollection
{
	public function loadAllQuestionsForSurvey ($surveyId) {
		if ($surveyId) {
			$sql = "SELECT * FROM survey_question WHERE survey_id = ? ORDER BY ordinal ASC";

			$data = Db_Pdo::fetchAll($sql, $surveyId);

			if ($data) {
				$this->build($data, new Survey_Question());
			}
		}
	}
}
