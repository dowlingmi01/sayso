<?php

class Survey_QuestionCollection extends RecordCollection
{
	// For surveys, polls and missions, this function loads all the questions for the given $surveyId
	// For trailers, it only loads the questions for the current trailer phase (based on survey trailer info)
	public function loadAvailableQuestionsForSurvey ($surveyId) {
		if ($surveyId) {
			$survey = new Survey();
			$survey->loadData($surveyId);

			if ($survey->type != "trailer") {
				$this->loadAllQuestionsForSurvey($surveyId);
				return true;
			}

			$sql = "
				SELECT id
				FROM survey_trailer_info
				WHERE survey_id = $surveyId
					AND ((start_date < now() AND end_date >= now()) OR (start_date is null))
			";
			$trailerInfo = Db_Pdo::fetch($sql);

			if ($trailerInfo && isset($trailerInfo['id'])) {
				$sql = "SELECT * FROM survey_question WHERE survey_id = ? AND survey_trailer_info_id = " . $trailerInfo['id'] . " ORDER BY ordinal ASC";

				$data = Db_Pdo::fetchAll($sql, $surveyId);

				if ($data) {
					$this->build($data, new Survey_Question());
					return true;
				}
			}

			// we should only reach here if: 1) there's no 'current' trailer info or 2) we couldn't load the questions for the survey
			throw new Exception("No trailer info found for trailer.");
		}
	}

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
