<?php

class ReportCell_UserConditionCollection extends RecordCollection
{
	public function loadAllForReportCell($reportCellId) {
		$sql = "SELECT *
				FROM report_cell_user_condition
				WHERE report_cell_id = ?
				";
		$reportCellUserConditions = Db_Pdo::fetchAll($sql, $reportCellId);

		if ($reportCellUserConditions) {
			$this->build($reportCellUserConditions, new ReportCell_UserCondition());
		}
	}
}
