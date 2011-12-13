<?php

class Survey extends Record
{
	protected $_tableName = 'survey';
	
	public static function getNextSurveyForUser($startSurvey, $userId) {
		// Figure out what the status of this survey is for this user
		$surveyUserMap = new Survey_UserMap();
		$surveyUserMap->loadDataByUniqueFields(array('survey_id' => $startSurvey->id, 'user_id' => $userId));
		if ($surveyUserMap->status) {
			$surveyUserStatus = $surveyUserMap->status;
		} else {
			$surveyUserStatus = 'new';
		}
		
		if ($surveyUserStatus == 'new' || $surveyUserStatus == 'archived') {
			$surveys = new SurveyCollection();
			$surveys->loadSurveysForStarbarAndUser($startSurvey->starbar_id, $userId, 'survey', $surveyUserStatus);
			$returnNextSurvey = false;
			foreach($surveys as $survey) {
				if ($returnNextSurvey) return $survey;
				if ($survey->id == $startSurvey->id) $returnNextSurvey = true;
			}
		}
		return new Survey();
	}
}

