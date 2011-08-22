<?php

class Lookup_Collection_SearchEngine extends RecordCollection implements LookupTable
{
    public function lookup ()
    {
        $records = Db_Pdo::fetchAll('SELECT * FROM lookup_search_engines');
        $this->build($records, new Lookup_SearchEngine());
    }
}
