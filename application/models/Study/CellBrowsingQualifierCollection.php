<?php
/**
 * @author alecksmart
 */

class Study_CellBrowsingQualifierCollection extends Collection
{
    public function loadForCell($cellId)
    {
        $sql = "SELECT
                    *
				FROM
                    study_cell_qualifier_browsing sb
				WHERE
                    sb.cell_id = ?";

        $entries = Db_Pdo::fetchAll($sql, $cellId);

        if ($entries)
        {
            $this->build($entries, new Study_CellBrowsingQualifier());
        }
    }
}

