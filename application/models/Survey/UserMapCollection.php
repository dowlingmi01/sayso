<?php

class Survey_UserMapCollection extends RecordCollection
{
	public function markUnseenSurveysNewForStarbarAndUser ($starbarId, $userId, $type, $maximumToDisplay) {
		$limitClause = "";
		
		$type = str_replace("surveys", "survey", $type);
		$type = str_replace("polls", "poll", $type);
		
		if ($maximumToDisplay) {
			$sql = "SELECT count(id) AS theCount
					FROM survey_user_map sum 
					JOIN survey s 
						ON s.id = sum.survey_id
						AND s.starbar_id = ?
						AND s.type = ?
					WHERE sum.user_id = ?
					";
			$result = Db_Pdo::fetch($sql, $starbarId, $type, $userId);
			$alreadyDisplayed = (int) $result['theCount'];

			if ($alreadyDisplayed >= $maximumToDisplay) return;
			$limitClause = " LIMIT ".($maximumToDisplay - $alreadyDisplayed);
		}
		
		if ($type == "poll" || $type == "survey") {
			$sql = "INSERT INTO survey_user_map (survey_id, user_id, status, created) 
						SELECT id, ?, 'new', now()
						FROM survey s
						WHERE type = ?
							AND id NOT IN (SELECT survey_id FROM survey_user_map WHERE user_id = ?)
							AND starbar_id = ?
						".$limitClause."
					";
			Db_Pdo::execute($sql, $userId, $type, $userId, $starbarId);
		}
	}

	public function markOldSurveysArchivedForStarbarAndUser ($starbarId, $userId, $type) {
		$type = str_replace("surveys", "survey", $type);
		$type = str_replace("polls", "poll", $type);
		$secondsBeforeAutoArchive = 86400; // one day
		
		if ($type == "poll" || $type == "survey") {
			$sql = "UPDATE survey_user_map sum
					JOIN survey s
						ON s.id = sum.survey_id
						AND s.type = ?
						AND s.starbar_id = ?
					SET status = 'archived'
					WHERE sum.user_id = ?
						AND status = 'new'
						AND ((UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(sum.created)) > ?)
					";
			Db_Pdo::execute($sql, $type, $starbarId, $userId, $secondsBeforeAutoArchive);
		}
	}
}
