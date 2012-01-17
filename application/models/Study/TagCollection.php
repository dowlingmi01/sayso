<?php

class Study_TagCollection extends RecordCollection
{

	public function loadForStudy($studyId)
	{
		$sql = "SELECT
					*
				FROM
					study_tag st
				WHERE
					st.study_id = ?";

		$entries = Db_Pdo::fetchAll($sql, $studyId);

		if ($entries)
		{
			$this->build($entries, new Study_Tag());
		}
	}

	public function loadForCreative($creativeId)
	{
		$sql = "SELECT
					st.*
				FROM
					study_tag st, study_creative_tag_map sctm
				WHERE
					sctm.creative_id = ?
				AND
				    st.id = sctm.tag_id";

		$entries = Db_Pdo::fetchAll($sql, $creativeId);

		if ($entries)
		{
			$this->build($entries, new Study_Tag());
		}
	}

	public static function dropForStudy($studyId)
	{
		$sql = "DELETE FROM study_tag WHERE study_id = ?";
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

