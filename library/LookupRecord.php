<?php

class LookupRecord extends Record implements Titled
{
    public function getTitle ()
    {
        return $this->label ? $this->label : ucwords(str_replace(array('_', '-'), ' ', $this->short_name));
    }
}
