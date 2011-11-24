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
}

