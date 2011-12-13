<?php
/**
 * @author alecksmart
 */
class Study_AvailDomainMap extends Record
{
	protected $_tableName = 'study_avail_domain_map';

	protected $_idKey = null;

	protected $_uniqueFields = array('study_avail_id' => 0, 'domain_id' => 0);

	public function init() {
		parent::init();
		// make sure zend db knows this table does not have an id col
		$this->setZendDbTableOptions(array(Zend_Db_Table_Abstract::PRIMARY => NULL));
	}
}

