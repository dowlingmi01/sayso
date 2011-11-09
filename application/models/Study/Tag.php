<?php


class Study_Tag extends Record
{
    protected $_tableName = 'study_tag';
    
    protected $_uniqueFields = array('tag' => '');
    
    /**
     * @var Study_Collection_Domain
     */
    protected $_domains;
    
    /**
     * @var Study_Collection_Creative
     */
    protected $_creatives;
    
    public function init () {
        $this->_domains = new Study_Collection_Domain();
        $this->_creatives = new Study_Collection_Creative();
        parent::init();
    }
    
    public function addDomain (Study_Domain $domain) {
        $this->_domains->addItem($domain);
    }
    
    public function getDomains () {
        return $this->_domains;
    }
    
    public function addCreative (Study_Creative $creative) {
        $this->_creatives->addItem($creative);
    }
    
    public function getCreatives () {
        return $this->_creatives;
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
    
    public function exportData() {
        $fields = array(
            'user_id',
            'name',
            'tag'
        );
        return array_intersect_key($this->getData(), array_flip($fields));
    }
    
    public function exportProperties($parentObject = null) {
        $props = array(
            '_domains' => $this->_domains,
            '_creatives' => $this->_creatives
        );
        return array_merge(parent::exportProperties($parentObject), $props);
    }
}

