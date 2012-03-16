<?php

class Survey_ResponseCollection extends RecordCollection
{
	public function markUnseenSurveysNewForStarbarAndUser ($starbarId, $userId, $type, $maximumToDisplay) {
		$limitClause = "";

		$type = str_replace("surveys", "survey", $type);
		$type = str_replace("polls", "poll", $type);

		if ($maximumToDisplay) {
			$sql = "SELECT count(id) AS theCount
					FROM survey_response sr
					JOIN survey s
						ON s.id = sr.survey_id
						AND s.starbar_id = ?
						AND s.type = ?
					WHERE sr.user_id = ?
					";
			$result = Db_Pdo::fetch($sql, $starbarId, $type, $userId);
			$alreadyDisplayed = (int) $result['theCount'];

			if ($alreadyDisplayed >= $maximumToDisplay) return;
			$limitClause = " LIMIT ".($maximumToDisplay - $alreadyDisplayed);
		}

		if ($type == "poll" || $type == "survey") {
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
						".$limitClause."
					";
			Db_Pdo::execute($sql, $type, $userId, $starbarId, $userId);
		}
	}

	public function markOldSurveysArchivedForStarbarAndUser ($starbarId, $userId, $type) {
		$type = str_replace("surveys", "survey", $type);
		$type = str_replace("polls", "poll", $type);
		$secondsBeforeAutoArchive = 259200; // = 72 hours = 3 days

		if ($type == "poll" || $type == "survey") {
			$sql = "UPDATE survey_response sr
					JOIN survey s
						ON s.id = sr.survey_id
						AND s.type = ?
						AND s.starbar_id = ?
						AND s.premium IS NOT TRUE
					SET status = 'archived'
					WHERE sr.user_id = ?
						AND status = 'new'
						AND ((UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(sr.created)) > ?)
					";
			Db_Pdo::execute($sql, $type, $starbarId, $userId, $secondsBeforeAutoArchive);
		}
	}
}
