<?php
/**
 * @author alecksmart
 */
class Study_QuotaCollection extends Collection
{

    public function loadForStudy($studyId)
    {
        $sql = "SELECT
                    *
				FROM
                    study_quota sq
				WHERE
                    sq.study_id = ?";

        $entries = Db_Pdo::fetchAll($sql, $studyId);

        if ($entries)
        {
            $this->build($entries, new Study_Quota());
        }
    }

    public static function dropForStudy($studyId)
    {
        $sql = "DELETE FROM study_quota WHERE study_id = ?";
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

