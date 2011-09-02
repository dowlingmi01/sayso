<?php


class Study_Creative extends Record
{
    protected $_tableName = 'study_creative';
    
    /**
     * Depending on the logic flow, tags attached
     * to this creative may need saving or may already
     * have been saved, in which case just save the 
     * mappings. Default is to do the most common thing
     * and save whatever collection is attached.
     * 
     * @var boolean
     */
    public static $saveTagsOnSave = true;
    
    /**
     * @var Study
     */
    protected $_study;
    
    /**
     * @var Study_Collection_Tag
     */
    protected $_tags;
    
    public function setStudy (Study $study) {
        $this->_study = $study;
    }
    
    public function addTag (Study_Tag $tag) {
        if (!$this->_tags) {
            $this->_tags = new Study_Collection_Tag();
        }
        $this->_tags->addItem($tag);
    }
    
    public function save() {
        parent::save();
        if ($this->_study) {
            $studyMap = new Study_CreativeMap();
            $studyMap->study_id = $this->_study->getId();
            $studyMap->creative_id = $this->getId();
            $studyMap->save();
        }
        if ($this->_tags) {
            if (self::$saveTagsOnSave) $this->_tags->save();
            // map tags to creatives
            foreach ($this->_tags as $tag) {
                $map = new Study_CreativeTagMap();
                $map->creative_id = $this->getId();
                $map->tag_id = $tag->getId();
                $map->save();
            }
        }
    }
}

