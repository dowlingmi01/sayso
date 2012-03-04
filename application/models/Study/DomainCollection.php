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

	public function loadAllDomainsForStudies($studies = null)
	{
		if ($studies) {
			$commaDelimitedStudyIdList = "";
			foreach($studies AS $study) {
				if ($commaDelimitedStudyIdList) $commaDelimitedStudyIdList = $commaDelimitedStudyIdList . ",";
				$commaDelimitedStudyIdList = $commaDelimitedStudyIdList . $study->id;
			}

			$sql = "
				SELECT sd.*
				FROM study_tag st
					LEFT JOIN study_tag_domain_map stdm ON st.id = stdm.tag_id
					LEFT JOIN study_domain sd ON sd.id = stdm.domain_id
				WHERE st.study_id IN (" . $commaDelimitedStudyIdList . ")
				ORDER BY FIND_IN_SET(st.study_id, '" . $commaDelimitedStudyIdList . "')
			";
			$domains = Db_Pdo::fetchAll($sql);

			if ($domains) {
				$this->build($domains, new Study_Domain());
			}
		}
	}
}

