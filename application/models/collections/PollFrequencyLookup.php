<?php

class PollFrequencyLookup extends RecordCollection implements LookupTable
{
    public function lookup ()
    {
        $pollFrequencies = Db_Pdo::fetchAll('SELECT * FROM lookup_poll_frequency');
        $this->build($pollFrequencies, new PollFrequency());
    }
}
