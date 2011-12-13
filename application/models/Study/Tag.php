<?php


class Study_Tag extends Record
{
	protected $_tableName = 'study_tag';
	
	protected $_uniqueFields = array('tag' => '');
	
	/**
	 * @var Study_DomainCollection
	 */
	protected $_domains;
	
	/**
	 * @var Study_CreativeCollection
	 */
	protected $_creatives;
	
	public function init () {
		$this->_domains = new Study_DomainCollection();
		$this->_creatives = new Study_CreativeCollection();
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
			'tag',
			'target_url'
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
	
	/**
	 * Get properties (used for serialization)
	 * 
	 * @see Object::serialize
	 * @return array
	 */
	protected function _getProperties() {
		return array_merge(array(
			'_domains' => $this->_domains,
			'_creatives' => $this->_creatives
		), parent::_getProperties());
	}
	
	/**
	 * Restore properties from array (used with serialization)
	 * 
	 * @see Object::unserialize
	 * @param array $properties
	 */
	protected function _restoreProperties (array $properties) {
		$this->_domains = $properties['_domains'];
		$this->_creatives = $properties['_creatives'];
		parent::_restoreProperties($properties);
	}
}

