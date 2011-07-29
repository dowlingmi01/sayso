<?php


class SurveyTypeLookup extends RecordCollection implements LookupTable
{
    public function lookup ()
    {
        $types = Db_Pdo::fetchAll('SELECT * FROM lookup_survey_type');
        $this->build($types, new SurveyType());
    }
}