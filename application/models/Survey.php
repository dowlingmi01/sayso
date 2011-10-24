<?php

class Survey extends Record
{
    protected $_tableName = 'survey';
    
    public static function getNextSurveyForUser($startSurvey, $userId) {
    	// Figure out what the status of this survey is for this user
    	$surveyUserMap = new Survey_UserMap();
    	$surveyUserMap->loadDataForSurveyAndUser($startSurvey->id, $userId);
    	if ($surveyUserMap->id) {
    		$surveyUserStatus = $surveyUserMap->status;
		} else {
			$surveyUserStatus = 'new';
		}
		
		if ($surveyUserStatus == 'new' || $surveyUserStatus == 'archived') {
			$surveys = new SurveyCollection();
			$surveys->loadSurveysForStarbarAndUser($startSurvey->starbar_id, $userId, 'survey', $surveyUserStatus);
			$i = 0;
			$numberOfSurveys = count($surveys);
			while ($i < $numberOfSurveys) {
				if ($surveys[$i]->id == $startSurvey->id && ($i < $numberOfSurveys - 1)) {
					return $surveys[$i+1];
				}
				$i++;
			}
		}
	}
}

