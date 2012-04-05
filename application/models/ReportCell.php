<?php

class ReportCell extends Record
{
	protected $_tableName = 'report_cell';

	public function process() {
		if (!$this->id) return;

		// Delete previous reporting data
		$reportCellSurveys = new ReportCell_SurveyCollection();
		$reportCellSurveys->deleteAllForReportCell($this->id);

		// @todo go through and_conditions and or_conditions to determine which users are in this group first
		// Currently, there is only one functional report cell, that has all the users.
		// The all users cell has no (i.e. null) comma_delimited_list_of_users. Should be non-null for other cells

		// Grab all surveys and process all of them
		$surveys = new SurveyCollection();
		$surveys->loadAllSurveys();

		foreach ($surveys as $survey) {
			// Allow 3 minutes to process each survey for each cell
			set_time_limit(180);
			$reportCellSurvey = new ReportCell_Survey();
			$reportCellSurvey->report_cell_id = $this->id;
			$reportCellSurvey->survey_id = $survey->id;

			$userArray = $survey->getArrayOfUsersWhoResponded($this->comma_delimited_list_of_users);
			if (count($userArray)) {
				$reportCellSurvey->number_of_responses = count($userArray);
				$reportCellSurvey->comma_delimited_list_of_users = ',' . implode(',', $userArray) . ',';
			}

			$reportCellSurvey->save();

			$reportCellSurvey->process();
		}
	}
}

