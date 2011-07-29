<?php

class EmailFrequency extends Record implements Titled
{
    protected $_tableName = 'lookup_email_frequency';

    public function getTitle ()
    {
        return $this->name;
    }

}