<?php


class Study_Tag extends Record
{
    protected $_tableName = 'study_tag';
    
    protected $_uniqueFields = array('tag' => '');
    
    /**
     * @var Study_Collection_Domain
     */
    protected $_domains;
    
    public function addDomain (Study_Domain $domain) {
        if (!$this->_domains) {
            $this->_domains = new Study_Collection_Domain();
        }
        $this->_domains->addItem($domain);
    }
    
    /**
     * Save tag and any domains attached
     * - also update the mapping table if necessary
     * @see Record::save()
     */
    public function save() {
        parent::save();
        if ($this->_domains) {
            $this->_domains->save();
            foreach ($this->_domains as $domain) {
                $map = new Study_TagDomainMap();
                $map->tag_id = $this->getId();
                $map->domain_id = $domain->getId();
                $map->save();
            }
        }
    }
    
    public function exportProperties($parentObject = null) {
        $props = array();
        if ($this->_domains) $props['_domains'] = $this->_domains;
        return array_merge(parent::exportProperties($parentObject), $props);
    }
}

