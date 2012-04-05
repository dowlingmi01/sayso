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
}
