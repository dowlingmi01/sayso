<?php

class ReportCell_SurveyCollection extends RecordCollection
{
	public function loadAllForReportCell($reportCellId) {
		$sql = "SELECT *
				FROM report_cell_survey
				WHERE report_cell_id = ?
				";
		$reportCellSurveys = Db_Pdo::fetchAll($sql, $reportCellId);

		if ($reportCellSurveys) {
			$this->build($reportCellSurveys, new ReportCell_Survey());
		}
	}

	public function deleteAllForReportCell($reportCellId) {
		$sql = "DELETE
				FROM report_cell_survey
				WHERE report_cell_id = ?
				";
		Db_Pdo::execute($sql, $reportCellId);
	}
}
