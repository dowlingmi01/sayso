<?php
/**
 * @author alecksmart
 */

class Study_CellSearchQualifierMapCollection extends Collection
{
	public function loadForQualifier($qualifierId)
	{
		$sql = "SELECT
					*
				FROM
					study_cell_qualifier_search_engines_map sm
				WHERE
					sm.cell_qualifier_search_id = ?";

		$entries = Db_Pdo::fetchAll($sql, $qualifierId);

		if ($entries)
		{
			$this->build($entries, new Study_CellSearchQualifierMap());
		}
	}
}

