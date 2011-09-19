<?php

class SurveyCollection extends RecordCollection
{
	/*
	* load polls or surveys for a specific user for a specific survey_user_status:
		* $user_id = get surveys for this user
		* $type = 'poll' or 'survey'
		* $survey_user_status = 'new', 'complete' or 'archived', i.e. has this user completed the survey, etc.
	*/
    public function loadSurveysForStarBarAndUser ($starbar_id, $user_id, $type, $survey_user_status)
    {
    	$order = "id ASC";
    	$surveys = null;

    	$type = str_replace("surveys", "survey", $type);
    	$type = str_replace("polls", "poll", $type);

    	$survey_user_status = str_replace("completed", "complete", $survey_user_status);
    	$survey_user_status = str_replace("archived", "archive", $survey_user_status);

    	switch ($survey_user_status)
    	{
    		case 'new':
				$sql = "SELECT *
						FROM survey
						WHERE survey.type = ?
							AND id NOT IN (SELECT survey_id FROM survey_user_map WHERE user_id = ?)
							AND survey.starbar_id = ?
						ORDER BY ?
						 ";
        		$surveys = Db_Pdo::fetchAll($sql, $type, $user_id, $starbar_id, $order);
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
						ORDER BY ?
						 ";
        		$surveys = Db_Pdo::fetchAll($sql, $user_id, $type, $starbar_id, $order);
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
						ORDER BY ?
						 ";
        		$surveys = Db_Pdo::fetchAll($sql, $user_id, $type, $starbar_id, $order);
    			break;
		}

		if ($surveys) {
        	$this->build($surveys, new Survey());
		}
    }
}
