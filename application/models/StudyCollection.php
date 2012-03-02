<?php
/**
 * @author alecksmart
 */

class StudyCollection extends RecordCollection
{
	public function loadAllFacebookStudies() {
		$sql = "SELECT *
				FROM study
				WHERE name LIKE 'FB AD %'
				";
		$studies = Db_Pdo::fetchAll($sql);

		if ($studies) {
			$this->build($studies, new Study());
		}
	}
}

