<?php
/**
 * @author alecksmart
 */

class StudyCollection extends RecordCollection
{
	public function loadAllTestStudies() {
		$sql = "SELECT id, name, study_type
				FROM study
				WHERE name LIKE '% AD: %'
				";
		$studies = Db_Pdo::fetchAll($sql);

		if ($studies) {
			$this->build($studies, new Study());
		}
	}
}

