<?php


class Study_Cell extends Record
{
    protected $_tableName = 'study_cell';
    
    /**
     * @var Study_Collection_Tag
     */
    protected $_tags;
    
    public function addTag (Study_Tag $tag) {
        if (!$this->_tags) {
            $this->_tags = new Study_Collection_Tag();
        }
        $this->_tags->addItem($tag);
    }
    
}

