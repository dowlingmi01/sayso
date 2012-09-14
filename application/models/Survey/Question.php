<?php

class Survey_Question extends Record
{
	protected $_tableName = 'survey_question';

	public $option_array; // Used during import process for convenience... not a DB field
	public $response_array; // Used during reporting preprocessing for convenience... not a DB field

	public function getCountOfUsersInReportCellWhoAnsweredThisQuestion($reportCellId = 1) {
		if (!$this->id) return;

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
		";

		$data = Db_Pdo::fetch($sql, $this->id);

		if ($data) return (int) $data['number_of_users'];
		return 0;
	}

	public function loadAllResponses($reportCellId = 1) {
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

		if ($reportCellId > 1) {
			$joinClause = " INNER JOIN survey_response sr ON sqr.survey_response_id = sr.id INNER JOIN report_cell_user_map rcum ON rcum.user_id = sr.user_id AND rcum.report_cell_id = ". $reportCellId . " ";
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

	public function getAverage($reportCellId = 1) {
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

		if ($reportCellId > 1) {
			$joinClause = " INNER JOIN survey_response sr ON sqr.survey_response_id = sr.id INNER JOIN report_cell_user_map rcum ON rcum.user_id = sr.user_id AND rcum.report_cell_id = ". $reportCellId . " ";
		} else { // filter out test users
			$joinClause = " INNER JOIN survey_response sr ON sqr.survey_response_id = sr.id INNER JOIN user u ON sr.user_id = u.id AND u.type != 'test' ";
		}

		// Select the field, and order the responses by that field so we can calculate the median
		$sql = "SELECT AVG(sqr." . $field . ") AS average_value FROM survey_question_response sqr " . $joinClause . " WHERE sqr.survey_question_id = ?";
		$result = Db_Pdo::fetch($sql, $this->id);
		return floatval($result['average_value']);
}

	public function getMedian($reportCellId = 1) {
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

		if ($reportCellId > 1) {
			$joinClause = " INNER JOIN survey_response sr ON sqr.survey_response_id = sr.id INNER JOIN report_cell_user_map rcum ON rcum.user_id = sr.user_id AND rcum.report_cell_id = ". $reportCellId . " ";
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

	public function getStandardDeviation($reportCellId = 1) {
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

		if ($reportCellId > 1) {
			$joinClause = " INNER JOIN survey_response sr ON sqr.survey_response_id = sr.id INNER JOIN report_cell_user_map rcum ON rcum.user_id = sr.user_id AND rcum.report_cell_id = ". $reportCellId . " ";
		} else { // filter out test users
			$joinClause = " INNER JOIN survey_response sr ON sqr.survey_response_id = sr.id INNER JOIN user u ON sr.user_id = u.id AND u.type != 'test' ";
		}

		// Select the field, and order the responses by that field so we can calculate the median
		$sql = "SELECT STDDEV_POP(sqr." . $field . ") AS standard_deviation_value FROM survey_question_response sqr " . $joinClause . " WHERE sqr.survey_question_id = ?";
		$result = Db_Pdo::fetch($sql, $this->id);
		return floatval($result['standard_deviation_value']);
	}

}
