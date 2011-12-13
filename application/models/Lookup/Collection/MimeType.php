<?php

class Lookup_Collection_MimeType extends RecordCollection implements LookupTable
{
	public function lookup ()
	{
		$records = Db_Pdo::fetchAll('SELECT * FROM lookup_mime_type');
		$this->build($records, new Lookup_MimeType());
	}
}
