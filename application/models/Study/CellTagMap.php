<?php


class Study_CellTagMap extends Record
{
    protected $_tableName = 'study_cell_tag_map';
    
    protected $_idKey = null;
    
    protected $_uniqueFields = array('cell_id' => 0, 'tag_id' => 0);
    
    public function init() {
        parent::init();
        // make sure zend db knows this table does not have an id col
        $this->setZendDbTableOptions(array(Zend_Db_Table_Abstract::PRIMARY => NULL));
    }
}

