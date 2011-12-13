<?php  

class Study_CellCollection extends RecordCollection
{

	public function loadForStudy($studyId)
	{
		$sql = "SELECT
					*
				FROM
					study_cell ss
				WHERE
					ss.study_id = ?";

		$entries = Db_Pdo::fetchAll($sql, $studyId);

		if ($entries)
		{
			$this->build($entries, new Study_Cell());
		}
	}

	public static function dropForStudy($studyId)
	{
		$sql = "DELETE FROM study_cell WHERE study_id = ?";
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