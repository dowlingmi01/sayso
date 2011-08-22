<?php

class Lookup_Collection_TimeFrame extends RecordCollection implements LookupTable
{
    public function lookup ()
    {
        $records = Db_Pdo::fetchAll('SELECT * FROM lookup_timeframe');
        $this->build($records, new Lookup_TimeFrame());
    }
}
