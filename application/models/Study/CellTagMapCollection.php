<?php
/**
 * @author sdaicz
 */

class Study_CellTagMapCollection extends Collection
{
	public function loadForCell($cellId)
	{
		$sql = "SELECT
					*
				FROM
					study_cell_tag_map sb
				WHERE
					sb.cell_id = ?";

		$entries = Db_Pdo::fetchAll($sql, $cellId);

		if ($entries)
		{
			$this->build($entries, new Study_CellTagMap());
		}
	}
}

