<?php

class Study_AdCollection extends RecordCollection
{
	public function loadAllStudyAds($userId) {
		$userFilterClause = "";
		if ($userId) {
			$userFilterClause = "
				INNER JOIN report_cell_user_map rcum
					ON (
						IFNULL(sa.report_cell_id, " . ReportCell::ALL_USERS_REPORT_CELL . ") = rcum.report_cell_id
						AND (rcum.report_cell_id = " . ReportCell::ALL_USERS_REPORT_CELL . " OR rcum.user_id = " . $userId . ")
					)
				WHERE sa.status = 'active'
			";
		}

		$sql = "SELECT sa.*
				FROM study_ad sa
				" . $userFilterClause . "
				ORDER BY sa.id ASC
				";
		$studyAds = Db_Pdo::fetchAll($sql);

		if ($studyAds) {
			$this->build($studyAds, new Study_Ad());
		}
	}
}

