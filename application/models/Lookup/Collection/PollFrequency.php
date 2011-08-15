<?php

class Lookup_Collection_PollFrequency extends RecordCollection implements LookupTable
{
    public function lookup ()
    {
        $pollFrequencies = Db_Pdo::fetchAll('SELECT * FROM lookup_poll_frequency');
        $this->build($pollFrequencies, new Lookup_PollFrequency());
    }
}
