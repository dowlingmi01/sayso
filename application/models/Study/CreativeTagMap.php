<?php


class Study_CreativeTagMap extends Record
{
	protected $_tableName = 'study_creative_tag_map';
	
	protected $_idKey = null;
	
	protected $_uniqueFields = array('creative_id' => 0, 'tag_id' => 0);
	
	public function init() {
		parent::init();
		// make sure zend db knows this table does not have an id col
		$this->setZendDbTableOptions(array(Zend_Db_Table_Abstract::PRIMARY => NULL));
	}
}

