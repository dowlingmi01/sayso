<?php


class Study_Cell extends Record
{
    protected $_tableName = 'study_cell';
    
    /**
     * @var Study_Collection_Tag
     */
    protected $_tags;
    
    public function init () {
        $this->_tags = new Study_Collection_Tag();
        parent::init();
    }
    
    public function addTag (Study_Tag $tag) {
        $this->_tags->addItem($tag);
    }
    
    public function getTags () {
        return $this->_tags;
    }
    
    public function exportData() {
        $fields = array(
            'description',
            'size',
            'cell_type'
        );
        return array_intersect_key($this->getData(), array_flip($fields));
    }
    
    public function exportProperties($parentObject = null) {
        $props = array(
            '_tags' => $this->_tags
        );
        return array_merge(parent::exportProperties($parentObject), $props);
    }
}

