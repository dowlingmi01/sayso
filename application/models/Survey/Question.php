<?php

class Survey_Question extends Record
{
	protected $_tableName = 'survey_question';

	public $option_array; // Used during import process for convenience... not a DB field
}
