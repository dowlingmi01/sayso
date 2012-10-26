<?php

class Survey_MissionInfo extends Record
{
	protected $_tableName = 'survey_mission_info';

	public function loadDataBySurveyId($surveyId) {
		$this->loadDataByUniqueFields(array('survey_id' => $surveyId));
	}
}
