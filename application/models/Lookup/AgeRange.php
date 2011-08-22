<?php


class Lookup_AgeRange extends LookupRecord
{
    protected $_tableName = 'lookup_age_range';
    /**
     * Override getTitle to piece together an 
     * appropriate title for age ranges
     * 
     * @return string 
     */
    public function getTitle() {
        return $this->age_from . ($this->age_to ? '-' . $this->age_to : '+');
        // example: 18-49 or 18+
    }
}

