<?php

class Survey_ResponseCollection extends RecordCollection
{
	static public function markUnseenSurveysNewForStarbarAndUser ($starbarId, $userId, $type, $maximumToDisplay) {
		$limitClause = "";

		$userId = intval($userId);

		$type = str_replace("surveys", "survey", $type);
		$type = str_replace("polls", "poll", $type);
		$type = str_replace("quizzes", "quiz", $type);
		$type = str_replace("trailers", "trailer", $type);

		if ($maximumToDisplay) {
			$sql = "SELECT count(sr.id) AS theCount
					FROM survey_response sr
					JOIN survey s
						ON s.id = sr.survey_id
						AND s.starbar_id = ?
						AND s.type = ?
						AND s.status = 'active'
					WHERE sr.user_id = ?
					";
			$result = Db_Pdo::fetch($sql, $starbarId, $type, $userId);
			$alreadyDisplayed = (int) $result['theCount'];

			if ($alreadyDisplayed >= $maximumToDisplay) return;
			$limitClause = " LIMIT ".($maximumToDisplay - $alreadyDisplayed);
		}

		// Calculate which survey days should be visible
		// If user joins on day 12 (after starbar launch), they should see surveys
		// for days 11 and 12. On the next day, they should see surveys for days 10 and 13. Etc.
		$starbarUserMap = new Starbar_UserMap();
		$starbarUserMap->loadDataByUniqueFields(array("user_id" => $userId, "starbar_id" => $starbarId));

		if (Registry::isRegistered('starbar')) {
			$starbar = Registry::getStarbar();
		} else {
			$starbar = new Starbar();
			$starbar->loadData($starbarId);
		}

		$userJoinedStarbar = strtotime($starbarUserMap->created);
		$starbarLaunched = strtotime($starbar->launched);

		if ($userJoinedStarbar < $starbarLaunched) $userJoinedStarbar = $starbarLaunched;

		$daysSinceUserJoinedStarbar = intval(floor((time() - $userJoinedStarbar) / 86400));
		$daysSinceStarbarLaunched = intval(floor((time() - $starbarLaunched) / 86400));
		$daysOfSurveysToDisplay = ($daysSinceUserJoinedStarbar + 1) * 2;

		$lastDayOfSurveysUserShouldSee = $daysSinceStarbarLaunched;
		$firstDayOfSurveysUserShouldSee = $lastDayOfSurveysUserShouldSee - $daysOfSurveysToDisplay + 1;
		if ($firstDayOfSurveysUserShouldSee < 1) $firstDayOfSurveysUserShouldSee = 1;
		if ($lastDayOfSurveysUserShouldSee < 1) $lastDayOfSurveysUserShouldSee = 1;

		if (in_array($type, array('survey', 'poll', 'quiz', 'trailer', 'mission'))) {
			$sql = "
				SELECT s.id
				FROM survey s
				INNER JOIN starbar_survey_map ssm
					ON s.id = ssm.survey_id
					AND ssm.starbar_id = ?
					AND ssm.start_at < now()
					AND (ssm.end_at > now() OR ssm.end_at = '0000-00-00 00:00:00')
					AND (
						(ssm.start_day >= ? AND ssm.start_day <= ?)
						OR
						(ssm.start_day IS NULL OR ssm.start_day = 0)
					)
				INNER JOIN report_cell_user_map rcum
					ON (
						IFNULL(s.report_cell_id, 1) = rcum.report_cell_id
						AND (rcum.report_cell_id = 1 OR rcum.user_id = ?)
					)
				RIGHT JOIN starbar_user_map sum
					ON sum.user_id = ?
					AND sum.starbar_id = ?
				WHERE s.type = ?
					AND s.id NOT IN (SELECT survey_id FROM survey_response WHERE user_id = ?)
					AND s.status = 'active'
					AND (
						(UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(sum.created)) > ssm.start_after
						OR
						ssm.start_after IS NULL
					)
				".$limitClause."
			";
			$newSurveyIds = Db_Pdo::fetchColumn($sql, $starbarId, $firstDayOfSurveysUserShouldSee, $lastDayOfSurveysUserShouldSee, $userId, $userId, $starbarId, $type, $userId);
			if (!$newSurveyIds || !count($newSurveyIds)) return;

			// Prepare and execute multi-row insert statement
			$sql = "";
			foreach ($newSurveyIds as $newSurveyId) {
				$newSurveyId = (int) $newSurveyId;
				if ($sql) $sql .= ", ";
				$sql .= "(" . $newSurveyId . ", " . $userId . ", 'new')";
			}

			Db_Pdo::execute("INSERT INTO survey_response (survey_id, user_id, status) VALUES ".$sql);
		}
	}

	static public function markOldSurveysArchivedForStarbarAndUser ($starbarId, $userId, $type) {
		$type = str_replace("surveys", "survey", $type);
		$type = str_replace("polls", "poll", $type);
		$secondsBeforeAutoArchive = 259200; // = 72 hours = 3 days

		if ($type == "poll" || $type == "survey") {
			$sql = "UPDATE survey_response sr
					JOIN survey s
						ON s.id = sr.survey_id
						AND s.type = ?
						AND s.starbar_id = ?
						AND s.reward_category = 'standard'
						AND s.status = 'active'
					SET sr.status = 'archived'
					WHERE sr.user_id = ?
						AND sr.status = 'new'
						AND ((UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(sr.created)) > ?)
					";
			Db_Pdo::execute($sql, $type, $starbarId, $userId, $secondsBeforeAutoArchive);
		}
	}

	public static function processAllResponsesPendingProcessing () {
		$surveyResponses = new Survey_ResponseCollection();
		$surveyResponses->loadAllResponsesPendingProcessing();
		$messages = array();
		foreach ($surveyResponses as $surveyResponse) {
			$messages = array_merge($messages, $surveyResponse->process());
		}
		return $messages;
	}

	public function loadAllResponsesPendingProcessing () {
		// Only select responses that were completed more than 20 minutes ago, since SG needs some time to process
		$sql = "SELECT *
				FROM survey_response
				WHERE processing_status = 'pending'
					AND ((UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(completed_disqualified)) > 1200)
				";
		$surveyResponses = Db_Pdo::fetchAll($sql);

		if ($surveyResponses) {
			$this->build($surveyResponses, new Survey_Response());
		}
	}

	public static function checkIfUserHasSurveys ($userId, $starbarId, $type, $status) {
		if (! in_array($type, array("survey", "poll", "quiz", "trailer", "mission"))) return false;

		// @todo the 0 on the following line should take into consideration the maximum to be displayed for that user for that starbar
		self::markUnseenSurveysNewForStarbarAndUser($starbarId, $userId, $type, 0);
		if (in_array($type, array("survey", "poll"))) self::markOldSurveysArchivedForStarbarAndUser($starbarId, $userId, $type);
		$surveyCollection = new SurveyCollection();
		$surveyCollection->loadSurveysForStarbarAndUser($starbarId, $userId, $type, $status);
		return $surveyCollection->count() > 0;
	}
}
