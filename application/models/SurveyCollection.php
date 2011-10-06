<?php

class SurveyCollection extends RecordCollection
{
	/*
	* load polls or surveys for a specific user for a specific $surveyUserStatus:
		* $userId = get surveys for this user
		* $type = 'poll' or 'survey'
		* $surveyUserStatus = 'new', 'complete' or 'archived', i.e. has this user completed the survey, etc.
	*/
    public function loadSurveysForStarbarAndUser ($starbarId, $userId, $type, $surveyUserStatus)
    {
    	$order = "ordinal ASC";
    	$surveys = null;

    	$type = str_replace("surveys", "survey", $type);
    	$type = str_replace("polls", "poll", $type);

    	$surveyUserStatus = str_replace("completed", "complete", $surveyUserStatus);
    	$surveyUserStatus = str_replace("archived", "archive", $surveyUserStatus);

    	switch ($surveyUserStatus)
    	{
    		case 'new':
				$sql = "SELECT *
						FROM survey
						WHERE survey.type = ?
							AND id NOT IN (SELECT survey_id FROM survey_user_map WHERE user_id = ?)
							AND survey.starbar_id = ?
						ORDER BY ".$order."
						 ";
        		$surveys = Db_Pdo::fetchAll($sql, $type, $userId, $starbarId);
    			break;

    		case 'complete':
				$sql = "SELECT *
						FROM survey
							INNER JOIN survey_user_map
							ON survey.id = survey_user_map.survey_id
								AND survey_user_map.user_id = ?
								AND survey_user_map.status = 'complete'
						WHERE survey.type = ?
							AND survey.starbar_id = ?
						ORDER BY ".$order."
						 ";
        		$surveys = Db_Pdo::fetchAll($sql, $userId, $type, $starbarId);
    			break;

    		case 'archive':
				$sql = "SELECT *
						FROM survey
							INNER JOIN survey_user_map
							ON survey.id = survey_user_map.survey_id
								AND survey_user_map.user_id = ?
								AND survey_user_map.status = 'archive'
						WHERE survey.type = ?
							AND survey.starbar_id = ?
						ORDER BY ".$order."
						 ";
        		$surveys = Db_Pdo::fetchAll($sql, $userId, $type, $starbarId);
    			break;
		}

		if ($surveys) {
        	$this->build($surveys, new Survey());
		}
    }
}
