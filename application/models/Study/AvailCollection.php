<?php
/**
 * @author alecksmart
 */
class Study_AvailCollection extends RecordCollection
{
    public function loadForCreative($creativeId)
    {
        $sql = "SELECT
                    *
				FROM
                    study_avail sa
				WHERE
                    sa.creative_id = ?";

        $entries = Db_Pdo::fetchAll($sql, $creativeId);

        if ($entries)
        {
            $this->build($entries, new Study_Avail());
        }
    }

}

