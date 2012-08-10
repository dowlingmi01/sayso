<?php

/**
* Create a select list showing references from another table
*
* @author Peter Connolly
*/
class Form_Element_Fkey extends Zend_Form_Element_Select
{
	/**
	* Indicates action being processed (e.g, edit, add, view, detail)
	*
	* @var mixed
	*/
	protected $_action;

	/**
	* Stores the options for this column
	*
	* @var array
	*/
	protected $_options;

	private $_newElements = array();

	private $_currentData = array();

	/**
	* Load a foreign-key association dropdown
	*
	* @param mixed $valuearray
	* Valuearray has to include the following key/value pairs;
	*	'lookuptable' - The table which the foreign keys are being extracted from
	*	'lookupfield' - The key field (usually ID) in the table - will be used as the index
	*	'lookuplabel' - The field which you want to see in the drop-down list
	*	'default' - A text string which will sit at the top of the list until a choice is made
	* @author Peter Connolly
	*/
	private function _setParams()
	{

		$db = Zend_Registry::get('db');
		$where = "";
		if (array_key_exists('where',$this->_options)) {
			$where = sprintf("WHERE %s",$this->_options['where']);
		}

	//	$sql = sprintf("SELECT %s as a, %s as b FROM %s %s ORDER BY %s ASC",$this->_options['lookupfield'],$this->_options['lookuplabel'],$this->_options['lookuptable'],$where, $this->_options['lookuplabel']);
		$sql = sprintf("SELECT %s as a, %s as b FROM %s %s ORDER BY %s",$this->_options['lookupfield'],$this->_options['lookuplabel'],$this->_options['lookuptable'],$where, $this->_options['lookuporder']);

		if ($this->_options['default']!== NULL) {
			$opts[]=$this->_options['default'];
		}

		$stmt = $db->query($sql);

		while ($row = $stmt->fetch()) {
			$opts[$row['a']] = $row['b'];
		}

		$this->setMultiOptions($opts);

		// Set default value if appropriate
		if (array_key_exists('pt',$this->_options['meta']) && array_key_exists('pi',$this->_options['meta']) && ($this->_options['meta']['pt']==$this->_options['lookuptable'])) {
			$this->setValue($this->_options['meta']['pi']);
			$this->setReadOnly($this->_options['lookuptable'],$this->_options['meta']['pi']);
		}

	}

	public function setReadOnly($hiddenfield=null,$hiddenvalue=null) {

		global $formElements;

		$this->setAttrib('disabled', 'disabled');
		// Create a new hidden field with the value in, because disabled
		$originalName = $this->getName();
		$this->setName($originalName."_hidden");
		if (($hiddenfield!=null) && ($hiddenvalue!=null)) {

			$this->_newElements[$originalName] = new Form_Element_Hidden($originalName,array('value'=>$hiddenvalue));
		} else {
			$this->_newElements[$originalName] = new Form_Element_Hidden($originalName,array('value'=>$this->_currentData[$originalName]));
		}

		$this->_newElements[$originalName]->buildElement("hide",$this->_options);
	}

	public function init()
	{
		$this->setLabel(ucwords(str_replace("_"," ",$this->getName())));
		return parent::init();
	}

	/**
	* Notify if we have any hidden elements
	*
	* @return Boolean - true if there is a hidden element, false otherwise
	*/
	public function hasHiddenElement()
	{
		return ($this->_newElements != null)?true:false;
	}

	/**
	* Returns the _newElements array, which contains any form fields added by this class
	*
	* @returns array _newElements
	*/
	public function getExtraElements()
	{
		return $this->_newElements;
	}

	private function _getAttribute($attrib)
	{

		if (array_key_exists('attributes',$this->_options)) {
			if (is_array($this->_options['attributes'])) {
			 	return in_array($attrib,$this->_options['attributes']);
			} else {
				return $this->_options['attributes'];
			}
		} else {
			return false;
		}
	}

	private function _setHelp()
	{
		$db = Zend_Registry::get('db');
		// Tooltip help

		if (array_key_exists('help',$this->_options)) {
			$this->setAttrib("title", $this->_options['help']);
		} else {
			// Nothing in the JSON. Check the table definition
			if (array_key_exists('tablename',$this->_options['meta']) && (array_key_exists('colname',$this->_options['meta']))) {
				$sql = sprintf("show full columns from %s where field = '%s'",$this->_options['meta']['tablename'],$this->_options['meta']['colname']);

				$coldetails = $db->fetchRow($sql);
				$this->setAttrib("title", $coldetails['Comment']);
			}
		}
	}

	public function buildElement($action,$optionarray,$currentData=null)
	{
		$this->_action = $action;
		$this->_options = $optionarray;
		$this->_currentData = $currentData;

		// Are we going to display this element?
		if (in_array($action,$this->_options['displaywhen'])) {

			$this->_setParams();
			$this->_setHelp();

			// We're going to display this element. Now look to see what we do with it
			switch ($action) {

				case "add" :
					break;

				case "detail" :
				case "delete" :
					$this->setReadOnly();
					break;

				case "edit":
					// Is it a writeonly field?
					if ($this->_getAttribute("writeonly")) {
						$this->setReadOnly();
					}
					break;
			}
		}

		return $this;
	}

}
?>