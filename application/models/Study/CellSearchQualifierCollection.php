<?php
/**
 * @author alecksmart
 */

class Study_CellSearchQualifierCollection extends Collection
{
    public function loadForCell($cellId)
    {
        $sql = "SELECT
                    *
				FROM
                    study_cell_qualifier_search ss
				WHERE
                    ss.cell_id = ?";

        $entries = Db_Pdo::fetchAll($sql, $cellId);

        if ($entries)
        {
            $this->build($entries, new Study_CellSearchQualifier());
        }
    }
}

