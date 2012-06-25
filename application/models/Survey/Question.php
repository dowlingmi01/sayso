<?php

class Survey_Question extends Record
{
	protected $_tableName = 'survey_question';

	public $option_array; // Used during import process for convenience... not a DB field
	public $response_array; // Used during reporting preprocessing for convenience... not a DB field

	public function getArrayOfUsersWhoAnsweredThisQuestion($commaDelimitedUserIdFilterList = null) {
		if (!$this->id) return;

		$sql = "
			SELECT sr.user_id
			FROM survey_response sr, survey_question_response sqr
			WHERE sqr.survey_response_id = sr.id
				AND sr.processing_status = 'completed'
				AND sqr.survey_question_id = ?
		";

		if ($commaDelimitedUserIdFilterList) {
			// add to $sql
			$sql .= " AND sr.user_id IN (" . trimCommas($commaDelimitedUserIdFilterList) . ")";
		}

		$sql .= " GROUP BY sr.user_id";

		return Db_Pdo::fetchColumn($sql, $this->id);
	}

	public function loadAllResponses($commaDelimitedUserIdFilterList = null) {
		if (!$this->id || !$this->data_type) return;

		switch ($this->data_type) {
			case "integer":
				$field = "response_integer";
				break;
			case "decimal":
			case "monetary":
				$field = "response_decimal";
				break;
			case "string":
				$field = "response_string";
				break;
			default:
				return;
				break;
		}

		$joinClause = "";

		if (trimCommas($commaDelimitedUserIdFilterList)) {
			$joinClause = " INNER JOIN survey_response sr ON sqr.survey_response_id = sr.id AND sr.user_id IN (" . trimCommas($commaDelimitedUserIdFilterList) . ") ";
		}

		// Select the field, and order the responses by that field so we can calculate the median
		$sql = "SELECT sqr." . $field . " FROM survey_question_response sqr " . $joinClause . " WHERE sqr.survey_question_id = ? ORDER BY " . $field;
		$responseArray = Db_Pdo::fetchColumn($sql, $this->id);

		// Cast integer and decimal/monetary values to the right type
		if ($this->data_type == 'integer' || $this->data_type == 'decimal' || $this->data_type == 'monetary') {
			foreach ($responseArray as &$response) {
				if ($this->data_type == 'integer') $response = intval($response);
				else $response = floatval($response);
			}
		}

		$this->response_array = $responseArray;
	}
}
