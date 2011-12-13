<?php


class Study_SurveyMap extends Record
{
	protected $_tableName = 'study_survey_map';
	
	protected $_uniqueFields = array('study_id' => 0, 'survey_id' => 0);
	
	// NOTE: different than other Sayso mapping tables, this one DOES have an id col
}

