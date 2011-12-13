<?php

class Study_CreativeCollection extends RecordCollection
{
	public function loadForStudy($studyId)
	{
		$sql = "SELECT
					sc.*
				FROM
					study_creative_map sm, study_creative sc
				WHERE
					sm.study_id = ? AND sm.creative_id = sc.id";

		$entries = Db_Pdo::fetchAll($sql, $studyId);

		if ($entries)
		{
			$this->build($entries, new Study_Creative);
		}
	}

	public static function dropForStudy($studyId)
	{
		$sql = "DELETE FROM study_creative_map WHERE study_id = ?";
		try
		{
			Db_Pdo::execute($sql, $studyId);
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}
		return false;
	}
}

