<?php

class Survey_Question extends Record
{
	protected $_tableName = 'survey_question';

	public $option_array; // Used during import process for convenience... not a DB field
	public $response_array; // Used during reporting preprocessing for convenience... not a DB field

	public function getStringOfUsersWhoAnsweredThisQuestion($commaDelimitedUserIdFilterList = null) {
		if (!$this->id) return;

		$sql = "
			SELECT GROUP_CONCAT(DISTINCT sr.user_id) AS user_id_list
			FROM survey_response sr, survey_question_response sqr
			WHERE sqr.survey_response_id = sr.id
				AND sr.processing_status = 'completed'
				AND sqr.survey_question_id = ?
		";

		if ($commaDelimitedUserIdFilterList) {
			// add to $sql
			$sql .= " AND sr.user_id IN (" . trimCommas($commaDelimitedUserIdFilterList) . ")";
		}

		$result = Db_Pdo::fetch($sql, $this->id);
		return $result['user_id_list'];
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
		} else { // filter out test users
			$joinClause = " INNER JOIN survey_response sr ON sqr.survey_response_id = sr.id INNER JOIN user u ON sr.user_id = u.id AND u.type != 'test' ";
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

	public function getAverage($commaDelimitedUserIdFilterList = null) {
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
			default:
				return;
				break;
		}

		$joinClause = "";

		if (trimCommas($commaDelimitedUserIdFilterList)) {
			$joinClause = " INNER JOIN survey_response sr ON sqr.survey_response_id = sr.id AND sr.user_id IN (" . trimCommas($commaDelimitedUserIdFilterList) . ") ";
		} else { // filter out test users
			$joinClause = " INNER JOIN survey_response sr ON sqr.survey_response_id = sr.id INNER JOIN user u ON sr.user_id = u.id AND u.type != 'test' ";
		}

		// Select the field, and order the responses by that field so we can calculate the median
		$sql = "SELECT AVG(sqr." . $field . ") AS average_value FROM survey_question_response sqr " . $joinClause . " WHERE sqr.survey_question_id = ?";
		$result = Db_Pdo::fetch($sql, $this->id);
		return floatval($result['average_value']);
}

	public function getMedian($commaDelimitedUserIdFilterList = null) {
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
			default:
				return;
				break;
		}

		$joinClause = "";

		if (trimCommas($commaDelimitedUserIdFilterList)) {
			$joinClause = " INNER JOIN survey_response sr ON sqr.survey_response_id = sr.id AND sr.user_id IN (" . trimCommas($commaDelimitedUserIdFilterList) . ") ";
		} else { // filter out test users
			$joinClause = " INNER JOIN survey_response sr ON sqr.survey_response_id = sr.id INNER JOIN user u ON sr.user_id = u.id AND u.type != 'test' ";
		}

		$sql = "SELECT COUNT(sqr." . $field . ") AS number_of_responses FROM survey_question_response sqr " . $joinClause . " WHERE sqr.survey_question_id = ?";
		$result = Db_Pdo::fetch($sql, $this->id);
		$numberOfResponses = intval($result['number_of_responses']);

		if (!$numberOfResponses) return;

		$medianOffset = intval(floor($numberOfResponses/2.0));

		$sql = "SELECT " . $field . " AS median_value FROM survey_question_response sqr " . $joinClause . " WHERE sqr.survey_question_id = ? ORDER BY " . $field . " LIMIT 1 OFFSET " . $medianOffset;
		$result = Db_Pdo::fetch($sql, $this->id);
		if ($this->data_type == "integer") {
			return intval($result['median_value']);
		} else {
			return floatval($result['median_value']);
		}
	}

	public function getStandardDeviation($commaDelimitedUserIdFilterList = null) {
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
			default:
				return;
				break;
		}

		$joinClause = "";

		if (trimCommas($commaDelimitedUserIdFilterList)) {
			$joinClause = " INNER JOIN survey_response sr ON sqr.survey_response_id = sr.id AND sr.user_id IN (" . trimCommas($commaDelimitedUserIdFilterList) . ") ";
		} else { // filter out test users
			$joinClause = " INNER JOIN survey_response sr ON sqr.survey_response_id = sr.id INNER JOIN user u ON sr.user_id = u.id AND u.type != 'test' ";
		}

		// Select the field, and order the responses by that field so we can calculate the median
		$sql = "SELECT STDDEV_POP(sqr." . $field . ") AS standard_deviation_value FROM survey_question_response sqr " . $joinClause . " WHERE sqr.survey_question_id = ?";
		$result = Db_Pdo::fetch($sql, $this->id);
		return floatval($result['standard_deviation_value']);
	}

}
