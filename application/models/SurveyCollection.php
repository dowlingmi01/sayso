<?php

class SurveyCollection extends RecordCollection
{
	/*
	* load polls or surveys for a specific user for a specific $surveyUserStatus:
		* $userId = get surveys for this user
		* $type = 'poll' or 'survey'
		* $surveyUserStatus = 'new', 'completed' or 'archived', i.e. has this user completed the survey, etc.
		* if $surveyUserStatus is null, it returns all Surveys for that user (i.e. all statuses)
	*/
	public function loadSurveysForStarbarAndUser ($starbarId, $userId, $type, $surveyUserStatus = null)
	{
		$orderSql = "FIELD (user_status, 'completed', 'disqualified', 'archived', 'new'), FIELD (reward_category, 'profile', 'premium', 'standard'), ssm.ordinal ASC";
		$userStatusSql = "";
		$trailerClause = "";
		$surveys = null;

		$type = str_replace("surveys", "survey", $type);
		$type = str_replace("polls", "poll", $type);
		$type = str_replace("trailers", "trailer", $type);
		$type = str_replace("quizzes", "quiz", $type);

		// We need to have a special clause for trailers, to check that there is a
		// valid survey_trailer_info record available for viewing
		if ($type=='trailer') {
			$trailerClause = "
				INNER JOIN survey_trailer_info sti
			    ON (
			    	sti.survey_id = s.id
			    	AND (sti.start_date<now() OR sti.start_date = '0000-00-00 00:00:00')
					AND (sti.end_date>now() OR sti.end_date = '0000-00-00 00:00:00')
			    )
			    ";
		}

		if ($surveyUserStatus && in_array($surveyUserStatus, array("new", "completed", "disqualified", "archived")))
			$userStatusSql = " AND sr.status = '".$surveyUserStatus."' ";

		$sql = "
			SELECT s.*, sr.status AS user_status
			FROM survey s
			INNER JOIN survey_response sr
				ON s.id = sr.survey_id
				AND sr.user_id = ?
				".$userStatusSql."
			INNER JOIN starbar_survey_map ssm
				ON s.id = ssm.survey_id
				AND ssm.starbar_id = ?
				AND ssm.start_at < now()
				AND (ssm.end_at > now() OR ssm.end_at = '0000-00-00 00:00:00')
			INNER JOIN report_cell_user_map rcum
				ON (
					IFNULL(s.report_cell_id, ?) = rcum.report_cell_id
					AND (rcum.report_cell_id = ? OR rcum.user_id = ?)
				)
			".$trailerClause."
			WHERE s.type = ?
				AND s.status = 'active'
			ORDER BY ".$orderSql."
		";
		$surveys = Db_Pdo::fetchAll($sql, $userId, $starbarId, ReportCell::ALL_USERS_REPORT_CELL, ReportCell::ALL_USERS_REPORT_CELL, $userId, $type);

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

	static public function getAllSurveysForAllStarbars($filterStarbarId = null) {
		$filterClause = "";
		if ($filterStarbarId) {
			$filterClause = " AND ssm.starbar_id = " . $filterStarbarId . " ";
		}

		$sql = "SELECT ssm.starbar_id AS unique_starbar_id, s.*
				FROM starbar_survey_map ssm, survey s
				WHERE s.id = ssm.survey_id
				" . $filterClause . "
				ORDER BY s.type ASC, s.reward_category DESC, s.id ASC
				";
		return Db_Pdo::fetchAll($sql);
	}
}
