<?php

class ReportCell_UserCondition extends Record
{
	protected $_tableName = 'report_cell_user_condition';

	// parent::_filter uses string_tags which breaks the '<=' comparison type
	protected function _filter ($value, $property = '') {
		return trim($value);
	}
}
