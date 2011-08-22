<?php

class Lookup_Collection_SocialActivityType extends RecordCollection implements LookupTable
{
    public function lookup ()
    {
        $records = Db_Pdo::fetchAll('SELECT * FROM lookup_social_activity_type');
        $this->build($records, new Lookup_SocialActivityType());
    }
}
