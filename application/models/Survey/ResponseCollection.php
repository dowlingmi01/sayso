<?php

class Survey_ResponseCollection extends RecordCollection
{
	static public function markUnseenSurveysNewForStarbarAndUser ($starbarId, $userId, $type, $maximumToDisplay) {
		$limitClause = "";

		$type = str_replace("surveys", "survey", $type);
		$type = str_replace("polls", "poll", $type);
		$type = str_replace("quizzes", "quiz", $type);

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
		$daysOfSurveysToDisplay = $daysSinceUserJoinedStarbar * 2;

		$lastDayOfSurveysUserShouldSee = $daysSinceStarbarLaunched + 1;
		$firstDayOfSurveysUserShouldSee = $lastDayOfSurveysUserShouldSee - $daysOfSurveysToDisplay;
		if ($firstDayOfSurveysUserShouldSee < 1) $firstDayOfSurveysUserShouldSee = 1;

		if ($type == "poll" || $type == "survey" || $type == "quiz") {
			$sql = "INSERT INTO survey_response (survey_id, user_id, status, created)
						SELECT s.id, u.id, 'new', now()
						FROM survey s, user u
						WHERE type = ?
							AND s.id NOT IN (SELECT survey_id FROM survey_response WHERE user_id = ?)
							AND s.starbar_id = ?
							AND s.start_at < now()
							AND (s.end_at > now() OR s.end_at = '0000-00-00 00:00:00')
							AND u.id = ?
							AND ((UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(u.created)) > s.start_after
								OR s.start_after IS NULL)
							AND s.start_day >= ?
							AND s.start_day <= ?
							AND s.status = 'active'
						".$limitClause."
					";
			Db_Pdo::execute($sql, $type, $userId, $starbarId, $userId, $firstDayOfSurveysUserShouldSee, $lastDayOfSurveysUserShouldSee);
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
}
