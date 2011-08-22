<?php


class Study_Tag extends Record
{
    protected $_tableName = 'study_tag';
    
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
                /* @var $domain Study_Domain */
                $mapping = new Study_TagDomainMap();
                $mapping->loadDataByUniqueFields(array(
                    'tag_id' => $this->getId(),
                    'domain_id' => $domain->getid()
                ));
                if (!$mapping->hasId()) $mapping->save();
                // where i got to: this isn't working. mappings are not getting saved
                // also the api is not returning a proper error message (just no_action)
            }
        }
    }
    
    public function exportProperties($parentObject = null) {
        $props = array();
        if ($this->_domains) $props['_domains'] = $this->_domains;
        return array_merge(parent::exportProperties($parentObject), $props);
    }
}

