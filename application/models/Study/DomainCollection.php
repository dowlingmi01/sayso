<?php

class Study_DomainCollection extends RecordCollection
{
	public function loadForTag($tagId)
	{
		$sql = "SELECT
					sd.*
				FROM
					study_domain sd, study_tag_domain_map st
				WHERE
					st.domain_id = sd.id
					AND st.tag_id = ?";

		$entries = Db_Pdo::fetchAll($sql, $tagId);

		if ($entries)
		{
			$this->build($entries, new Study_Domain());
		}
	}

	public function loadForAvail($availId)
	{
		$sql = "SELECT
					sd.*
				FROM
					study_domain sd, study_avail_domain_map am
				WHERE
					am.domain_id = sd.id
					AND am.study_avail_id = ?";

		$entries = Db_Pdo::fetchAll($sql, $availId);

		if ($entries)
		{
			$this->build($entries, new Study_Domain());
		}
	}
}

