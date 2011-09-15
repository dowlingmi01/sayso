<?php

class SurveyCollection extends RecordCollection
{
	/*
	* get polls or surveys for a specific user for a specific tab:
		* $userId = get surveys for this user
		* $type = 'poll' or 'survey'
		* $tab = 'new', 'complete' or 'archived'
	*/
    public function getSurveysForUser ($userId, $type, $tab)
    {
    	$order = "id ASC";
    	$surveys = null;

    	$type = str_replace("surveys", "survey", $type);
    	$type = str_replace("polls", "poll", $type);

    	$tab = str_replace("completed", "complete", $tab);
    	$tab = str_replace("archived", "archive", $tab);

    	switch ($tab)
    	{
    		case 'new':
				$sql = "SELECT *
						FROM survey
						WHERE survey.type = ?
							AND id NOT IN (SELECT survey_id FROM survey_user_map WHERE user_id = ?)
						ORDER BY ?
						 ";
        		$surveys = Db_Pdo::fetchAll($sql, $type, $userId, $order);
    			break;

    		case 'complete':
				$sql = "SELECT *
						FROM survey	LEFT JOIN survey_user_map
							ON survey.id = survey_user_map.survey_id
							AND survey_user_map.user_id = ?
							AND survey_user_map.response_id IS NOT NULL
						WHERE survey.type = ?
						ORDER BY ?
						 ";
        		$surveys = Db_Pdo::fetchAll($sql, $userId, $type, $order);
    			break;

    		case 'archive':
				$sql = "SELECT *
						FROM survey	LEFT JOIN survey_user_map
							ON survey.id = survey_user_map.survey_id
							AND survey_user_map.user_id = ?
							AND survey_user_map.response_id IS NULL
						WHERE survey.type = ?
						ORDER BY ?
						 ";
        		$surveys = Db_Pdo::fetchAll($sql, $userId, $type, $order);
    			break;
		}

		if ($surveys) {
        	$this->build($surveys, new Survey());
		}
    }
}
