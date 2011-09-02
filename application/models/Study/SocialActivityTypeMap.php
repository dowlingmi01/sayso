<?php


class Study_SocialActivityTypeMap extends Record
{
    protected $_tableName = 'study_social_activity_type_map';
    
    protected $_idKey = null;
    
    protected $_uniqueFields = array('study_id' => 0, 'social_activity_type_id' => 0);
    
    public function init() {
        parent::init();
        // make sure zend db knows this table does not have an id col
        $this->setZendDbTableOptions(array(Zend_Db_Table_Abstract::PRIMARY => NULL));
    }
}

