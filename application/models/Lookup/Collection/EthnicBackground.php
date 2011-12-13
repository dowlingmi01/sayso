<?php

class Lookup_Collection_EthnicBackground extends RecordCollection implements LookupTable
{
	public function lookup ()
	{
		$records = Db_Pdo::fetchAll('SELECT * FROM lookup_ethnicity');
		$this->build($records, new Lookup_EthnicBackground());
	}
}
