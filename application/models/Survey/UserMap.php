<?php


class Survey_UserMap extends Record
{
    protected $_tableName = 'survey_user_map';
    
    protected $_idKey = null;
    
    protected $_uniqueFields = array('survey_id' => 0, 'user_id' => 0);
    
    public function init() {
        parent::init();
        // make sure zend db knows this table does not have an id col
        $this->setZendDbTableOptions(array(Zend_Db_Table_Abstract::PRIMARY => NULL));
    }
}

