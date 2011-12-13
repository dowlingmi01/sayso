<?php


class Lookup_Collection_SurveyType extends RecordCollection implements LookupTable
{
	public function lookup ()
	{
		$types = Db_Pdo::fetchAll('SELECT * FROM lookup_survey_type');
		$this->build($types, new Lookup_SurveyType());
	}
}