<?php


class Study_SurveyCriterion extends Record
{
	protected $_tableName = 'study_survey_criterion';
	
	protected $_uniqueFields = array('site' => '', 'timeframe_id' => 0);
}

