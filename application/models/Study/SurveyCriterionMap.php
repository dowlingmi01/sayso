<?php


class Study_SurveyCriterionMap extends Record
{
    protected $_tableName = 'study_survey_criterion_map';
    
    protected $_idKey = null;
    
    protected $_uniqueFields = array('study_survey_map_id' => 0, 'survey_criterion_id' => 0);
    
    public function init() {
        parent::init();
        // make sure zend db knows this table does not have an id col
        $this->setZendDbTableOptions(array(Zend_Db_Table_Abstract::PRIMARY => NULL));
    }
}

