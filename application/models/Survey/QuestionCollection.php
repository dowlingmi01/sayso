<?php

class Survey_QuestionCollection extends RecordCollection
{
	public function loadAllQuestionsForSurvey ($surveyId) {
		if ($surveyId) {
			$survey = new Survey();
			$survey->loadData($surveyId);
			if ($survey->type == "trailer") {
				$sql = "
					SELECT id
					FROM survey_trailer_info
					WHERE survey_id = $surveyId
						AND ((start_date < now() AND end_date >= now()) OR (start_date is null))
				";
				$trailerInfo = Db_Pdo::fetch($sql);

				if ($trailerInfo && isset($trailerInfo['id']))
					$sql = "SELECT * FROM survey_question WHERE survey_id = ? AND survey_trailer_info_id = " . $trailerInfo['id'] . " ORDER BY ordinal ASC";
				else
					throw new Exception("No trailer info found for trailer.");

			} else {
				$sql = "SELECT * FROM survey_question WHERE survey_id = ? ORDER BY ordinal ASC";
			}

			$data = Db_Pdo::fetchAll($sql, $surveyId);

			if ($data) {
				$this->build($data, new Survey_Question());
			}
		}
	}
}
