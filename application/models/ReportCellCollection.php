<?php

class ReportCellCollection extends RecordCollection
{
	public function loadAllReportCells() {
		$sql = "SELECT *
				FROM report_cell
				";
		$reportCells = Db_Pdo::fetchAll($sql);

		if ($reportCells) {
			$this->build($reportCells, new ReportCell());
		}
	}
}

