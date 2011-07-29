<?php

class SurveyType extends Record implements Titled
{
    protected $_tableName = 'lookup_survey_type';
    
    public function getTitle ()
    {
        return $this->name;
    }
}
