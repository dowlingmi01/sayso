<?php

class Study_AdCollection extends RecordCollection
{
	public function loadAllStudyAds() {
		$sql = "SELECT *
				FROM study_ad
				ORDER BY id ASC
				";
		$studyAds = Db_Pdo::fetchAll($sql);

		if ($studyAds) {
			$this->build($studyAds, new Study_Ad());
		}
	}
}

