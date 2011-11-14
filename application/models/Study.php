<?php


class Study extends Record
{
    protected $_tableName = 'study';
    
    /**
     * @var Study_CellCollection
     */
    protected $_cells;
    
    public function init () {
        $this->_cells = new Study_CellCollection();
        parent::init();
    }
    
    public function addCell (Study_Cell $cell) {
        $this->_cells->addItem($cell);
    }
    
    /**
     * @var Study_CellCollection
     */
    public function getCells () {
        return $this->_cells;
    }
    
    public function exportData() {
        $fields = array(
            'user_id',
            'name',
            'description',
            'size',
            'size_minimum',
            'begin_date',
            'end_date',
            'click_track'
        );
        return array_intersect_key($this->getData(), array_flip($fields));
    }
    
    public function exportProperties($parentObject = null) {
        $props = array(
            '_cells' => $this->_cells
        );
        return array_merge(parent::exportProperties($parentObject), $props);
    }
}

