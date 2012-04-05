<?php

class ReportCell_SurveyCalculationCollection extends RecordCollection
{
	public function loadAllCalculationsForReportCellSurvey($reportCellSurveyId) {
		$sql = "SELECT *
				FROM report_cell_survey_calculation
				WHERE report_cell_survey_id = ?
				";
		$reportCellSurveyCalculations = Db_Pdo::fetchAll($sql, $reportCellSurveyId);

		if ($reportCellSurveyCalculations) {
			$this->build($reportCellSurveyCalculations, new ReportCell_SurveyCalculation());
		}
	}

	public function deleteAllCalculationsForReportCellSurvey($reportCellSurveyId) {
		$sql = "DELETE
				FROM report_cell_survey_calculation
				WHERE report_cell_survey_id = ?
				";
		Db_Pdo::execute($sql, $reportCellSurveyId);
	}
}
