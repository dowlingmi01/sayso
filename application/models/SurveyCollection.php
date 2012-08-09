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
		$optionalFilterSql = "";
		$surveys = null;

		$type = str_replace("surveys", "survey", $type);
		$type = str_replace("polls", "poll", $type);
		$type = str_replace("trailers", "trailer", $type);
		$type = str_replace("quizzes", "quiz", $type);

		if ($surveyUserStatus && in_array($surveyUserStatus, array("new", "completed", "disqualified", "archived")))
			$userStatusSql = " AND sr.status = '".$surveyUserStatus."' ";

		if ($type == "survey") {
			$profileSurvey = new Survey();
			$profileSurvey->loadProfileSurveyForStarbar($starbarId);
			// Only show premium surveys to people who have completed the profile survey
			if (!Survey_Response::checkIfUserHasCompletedSurvey($userId, $profileSurvey->id))
				$optionalFilterSql = " AND s.reward_category != 'premium' ";
				$optionalFilterSql .= " AND s.requires_age IS NOT TRUE ";
		}

		$sql = "SELECT s.*, sr.status AS user_status
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
				WHERE s.type = ?
					AND s.status = 'active'
					".$optionalFilterSql."
				ORDER BY ".$orderSql."
				 ";
		$surveys = Db_Pdo::fetchAll($sql, $userId, $starbarId, $type);

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
