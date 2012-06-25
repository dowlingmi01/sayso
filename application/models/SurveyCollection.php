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
		$order = "ssm.ordinal ASC";
		$surveys = null;

		$type = str_replace("surveys", "survey", $type);
		$type = str_replace("polls", "poll", $type);

		$sql = "SELECT s.*
				FROM survey s
					INNER JOIN survey_response sr
					ON s.id = sr.survey_id
						AND sr.user_id = ?
						AND sr.status = ?
					INNER JOIN starbar_survey_map ssm
					ON s.id = ssm.survey_id
						AND ssm.starbar_id = ?
						AND ssm.start_at < now()
						AND (ssm.end_at > now() OR ssm.end_at = '0000-00-00 00:00:00')
				WHERE s.type = ?
					AND s.status = 'active'
				ORDER BY ".$order."
				 ";
		$surveys = Db_Pdo::fetchAll($sql, $userId, $surveyUserStatus, $starbarId, $type);

		if ($surveys) {
			$this->build($surveys, new Survey());
		}
	}

	public function loadAllSurveys() {
		$sql = "SELECT *
				FROM survey
				ORDER BY type ASC, reward_category DESC, id ASC
				";
		$surveys = Db_Pdo::fetchAll($sql);

		if ($surveys) {
			$this->build($surveys, new Survey());
		}
	}
}
