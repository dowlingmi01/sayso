<?php

class SurveyCollection extends RecordCollection
{
	/*
	* load polls or surveys for a specific user for a specific $surveyUserStatus:
		* $userId = get surveys for this user
		* $type = 'poll' or 'survey'
		* $surveyUserStatus = 'new', 'completed' or 'archived', i.e. has this user completed the survey, etc.
	*/
	public function loadSurveysForStarbarAndUser ($starbarId, $userId, $type, $surveyUserStatus)
	{
		$order = "s.ordinal ASC";
		$surveys = null;

		$type = str_replace("surveys", "survey", $type);
		$type = str_replace("polls", "poll", $type);

		$sql = "SELECT *
				FROM survey s
					INNER JOIN survey_response sr
					ON s.id = sr.survey_id
						AND sr.user_id = ?
						AND sr.status = ?
				WHERE s.type = ?
					AND s.starbar_id = ?
					AND s.start_at < now()
					AND (s.end_at > now() OR s.end_at = '0000-00-00 00:00:00')
				ORDER BY ".$order."
				 ";
		$surveys = Db_Pdo::fetchAll($sql, $userId, $surveyUserStatus, $type, $starbarId);

		if ($surveys) {
			$this->build($surveys, new Survey());
		}
	}

	public function loadAllSurveys() {
		$sql = "SELECT *
				FROM survey
				ORDER BY type DESC, ordinal ASC
				";
		$surveys = Db_Pdo::fetchAll($sql);

		if ($surveys) {
			$this->build($surveys, new Survey());
		}
	}
}
