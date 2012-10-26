<?php

class Survey_MissionProgress extends Record
{
	protected $_tableName = 'survey_mission_progress';
	protected $_uniqueFields = array('survey_id' => 0, 'user_id' => 0, 'top_frame_id' => 0);
}
