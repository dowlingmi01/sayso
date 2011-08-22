<?php


class Lookup_QuotaPercentile extends LookupRecord
{
    protected $_tableName = 'lookup_quota_percentile';
    public function getTitle() {
        return $this->quota . '%';
    }
}

