<?php


class Study_Cell extends Record
{
	protected $_tableName = 'study_cell';
	
	/**
	 * @var Study_TagCollection
	 */
	protected $_tags;
	
	public function init () {
		$this->_tags = new Study_TagCollection();
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
	
	/**
	 * Get properties (used for serialization)
	 * 
	 * @see Object::serialize
	 * @return array
	 */
	protected function _getProperties() {
		return array_merge(array(
			'_tags' => $this->_tags
		), parent::_getProperties());
	}
	
	/**
	 * Restore properties from array (used with serialization)
	 * 
	 * @see Object::unserialize
	 * @param array $properties
	 */
	protected function _restoreProperties (array $properties) {
		$this->_tags = $properties['_tags'];
		parent::_restoreProperties($properties);
	}
}

