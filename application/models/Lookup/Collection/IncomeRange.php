<?php

class Lookup_Collection_IncomeRange extends RecordCollection implements LookupTable
{
    public function lookup ()
    {
        $records = Db_Pdo::fetchAll('SELECT * FROM lookup_income_range');
        $this->build($records, new Lookup_IncomeRange());
    }
}
