<?php
/**
 *Browser type class
 *
 * Handles functions related to the browser_type table
 */

class Browser_Type extends Record
{
	/**
	 *Table name
	 *
	 * @var string
	 */
	protected $_tableName = 'browser_type';

	/**
	 * Unique fields for this table
	 *
	 * @var array
	 */
	protected $_uniqueFields = array('name' => '');

	/**
	 * Processes a browser type to return its db value or add a new one
	 *
	 * @param string $name
	 */
	public function processBrowser($name)
	{
		$browser = Db_Pdo::fetch('SELECT id FROM ' . $this->_tableName . ' WHERE name = ?', $name);

		if (isset($browser["id"]))
			$this->loadData ($browser["id"]);
		else {
			$this->name = $name;
			$this->save();
		}
	}
}