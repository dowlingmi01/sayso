<?php


class Study_CellSearchQualifierMap extends Record
{
    protected $_tableName = 'study_cell_qualifier_search_engines_map';
    
    protected $_idKey = null;
    
    protected $_uniqueFields = array('cell_qualifier_search_id' => 0, 'search_engines_id' => 0);
    
    public function init() {
        parent::init();
        // make sure zend db knows this table does not have an id col
        $this->setZendDbTableOptions(array(Zend_Db_Table_Abstract::PRIMARY => NULL));
    }
}

