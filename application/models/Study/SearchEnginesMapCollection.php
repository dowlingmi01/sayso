<?php

class Study_SearchEnginesMapCollection extends Collection
{

    public function loadForStudy($studyId)
    {
        $sql = "SELECT
                    *
				FROM
                    study_search_engines_map sm
				WHERE
                    sm.study_id = ?";

        $entries = Db_Pdo::fetchAll($sql, $studyId);

        if ($entries)
        {
            $this->build($entries, new Study_SearchEnginesMap());
        }
    }

    public static function dropForStudy($studyId)
    {
        $sql = "DELETE FROM study_search_engines_map WHERE study_id = ?";
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

