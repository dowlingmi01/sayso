<?php

class Lookup_Collection_AgeRange extends RecordCollection implements LookupTable
{
	public function lookup ()
	{
		$records = Db_Pdo::fetchAll('SELECT * FROM lookup_age_range');
		$this->build($records, new Lookup_AgeRange());
	}
}
