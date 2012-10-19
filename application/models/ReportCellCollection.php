<?php

class ReportCellCollection extends RecordCollection
{
	public function loadAllReportCells($filterStarbarId = null) {
		$filterClause = "";
		if ($filterStarbarId) {
			$filterClause = " LEFT JOIN report_cell_user_condition rcuc ON rcuc.report_cell_id = rc.id WHERE rc.category != 'study' AND (rcuc.compare_starbar_id IS NULL OR rcuc.compare_starbar_id = " . $filterStarbarId . ")";
		}

		$sql = "SELECT rc.*
				FROM report_cell rc
				" . $filterClause . "
				ORDER BY FIELD(rc.category, 'Internal', 'Panel', 'Gender', 'Age Range', 'Marital Status', 'Education', 'Ethnicity', 'Industry', 'Income', 'Parental Status', 'Geography', 'Custom', 'Study'), rc.id ASC
				";
		$reportCells = Db_Pdo::fetchAll($sql);

		if ($reportCells) {
			$this->build($reportCells, new ReportCell());
		}
	}

	public function processAllSurveys() {
		foreach ($this->_items as $reportCell) {
			$reportCell->processAllSurveys();
		}
	}

	public function processConditions() {
		foreach ($this->_items as $reportCell) {
			$reportCell->processConditions();
		}
	}

	public function loadUnprocessedReportCellsThatHaveNoUnprocessedDependencies() {
		$sql = "SELECT *
				FROM report_cell
				WHERE conditions_processed IS NOT true
				AND processing_type = 'automatic'
				AND id NOT IN (

					SELECT rc.id
					FROM report_cell rc
					INNER JOIN report_cell_user_condition rcuc
						ON rcuc.report_cell_id = rc.id
					INNER JOIN report_cell rc_dependency
						ON rcuc.compare_report_cell_id = rc_dependency.id
					WHERE rc_dependency.conditions_processed IS NOT true

				)
				";
		$reportCells = Db_Pdo::fetchAll($sql);

		if ($reportCells) {
			$this->build($reportCells, new ReportCell());
		}
	}

	static public function processAllReportCellConditions() {
		$sql = "UPDATE report_cell SET conditions_processed = 0";
		Db_Pdo::execute($sql);

		$unprocessedReportCellsThatCanBeProcessed = new ReportCellCollection();
		$unprocessedReportCellsThatCanBeProcessed->loadUnprocessedReportCellsThatHaveNoUnprocessedDependencies();
		while (sizeof($unprocessedReportCellsThatCanBeProcessed)) {
			$unprocessedReportCellsThatCanBeProcessed->processConditions();
			$unprocessedReportCellsThatCanBeProcessed = new ReportCellCollection();
			$unprocessedReportCellsThatCanBeProcessed->loadUnprocessedReportCellsThatHaveNoUnprocessedDependencies();
		}
	}
}

