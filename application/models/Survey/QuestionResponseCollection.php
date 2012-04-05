<?php

class Survey_QuestionResponseCollection extends RecordCollection
{
	public function loadAllNumericResponsesForSurvey ($surveyId, $commaDelimitedUserIdFilterList = null) {
		if ($surveyId) {
			$userFilterClause = "";

			if ($commaDelimitedUserIdFilterList) {
				$userFilterClause = " AND sr.user_id IN (" . trimCommas($commaDelimitedUserIdFilterList) . ")";
			}

			$sql = "
				SELECT sqr.*
				FROM survey_question_response sqr
				INNER JOIN survey_response sr
					ON sqr.survey_response_id = sr.id
					AND sr.survey_id = ?
					" . $userFilterClause . "
				WHERE (
					sqr.data_type = 'integer'
					OR sqr.data_type = 'decimal'
					OR sqr.data_type = 'monetary'
					)
				ORDER BY response_integer ASC, response_decimal ASC
			";

			// The responses are ordered by the response values so we can easily calculate the median

			$data = Db_Pdo::fetchAll($sql, $surveyId);

			if ($data) {
				$this->build($data, new Survey_QuestionResponse());
			}
		}
	}


	public function loadAllDataResponsesForSurveyQuestion ($surveyQuestionId, $commaDelimitedUserIdFilterList = null) {
		if ($surveyQuestionId) {
			$userFilterClause = "";

			if ($commaDelimitedUserIdFilterList) {
				$userFilterClause = "
					INNER JOIN survey_response sr
						ON sqr.survey_response_id = sr.id
						AND sr.user_id = IN (" . trimCommas($commaDelimitedUserIdFilterList) . ")
				";
			}

			$sql = "
				SELECT sqr.*
				FROM survey_question_response sqr
				" . $userFilterClause . "
				WHERE sqr.data_type != 'choice'
					AND sqr.survey_question_id = ?
				ORDER BY response_integer ASC, response_decimal ASC, response_string ASC
			";
			// The responses are ordered by the response values for display purposes

			$data = Db_Pdo::fetchAll($sql, $surveyQuestionId);

			if ($data) {
				$this->build($data, new Survey_QuestionResponse());
			}
		}
	}

}
