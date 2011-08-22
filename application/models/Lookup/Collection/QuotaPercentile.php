<?php

class Lookup_Collection_QuotaPercentile extends RecordCollection implements LookupTable
{
    public function lookup ()
    {
        $records = Db_Pdo::fetchAll('SELECT * FROM lookup_quota_percentile');
        $this->build($records, new Lookup_QuotaPercentile());
    }
}
