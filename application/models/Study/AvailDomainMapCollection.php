<?php
/**
 * @author alecksmart
 */
class Study_AvailDomainMapCollection extends RecordCollection
{
	public function loadForAvail($availId)
	{
		$sql = "SELECT
					*
				FROM
					study_avail_domain_map adm, study_domain sd
				WHERE
					adm.study_avail_id = ? AND adm.domain_id = sd.id";

		$entries = Db_Pdo::fetchAll($sql, $availId);

		if ($entries)
		{
			$this->build($entries, new Study_Domain);
		}
	}

}

