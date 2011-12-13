<?php

class Lookup_Collection_Gender extends RecordCollection implements LookupTable
{
	public function lookup ()
	{
		$records = Db_Pdo::fetchAll('SELECT * FROM lookup_gender');
		$this->build($records, new Lookup_Gender());
	}
}
