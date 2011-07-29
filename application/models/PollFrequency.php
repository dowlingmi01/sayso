<?php

class PollFrequency extends Record implements Titled
{
    protected $_tableName = 'lookup_poll_frequency';
    
    public function getTitle ()
    {
        return $this->name;
    }
}