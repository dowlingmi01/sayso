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
					INNER JOIN survey_user_map sum
					ON s.id = sum.survey_id
						AND sum.user_id = ?
						AND sum.status = ?
				WHERE s.type = ?
					AND s.starbar_id = ?
				ORDER BY ".$order."
				 ";
        $surveys = Db_Pdo::fetchAll($sql, $userId, $surveyUserStatus, $type, $starbarId);

		if ($surveys) {
        	$this->build($surveys, new Survey());
		}
    }
}
