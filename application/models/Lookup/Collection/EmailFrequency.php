<?php

class Lookup_Collection_EmailFrequency extends RecordCollection implements LookupTable
{
	public function lookup ()
	{
		$frequencies = Db_Pdo::fetchAll('SELECT * FROM lookup_email_frequency');
		$this->build($frequencies, new Lookup_EmailFrequency());
	}
}
