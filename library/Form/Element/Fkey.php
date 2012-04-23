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
		$sql = sprintf("SELECT %s as a, %s as b FROM %s ORDER BY %s ASC",$this->_options['lookupfield'],$this->_options['lookuplabel'],$this->_options['lookuptable'],$this->_options['lookuplabel']);
		
		if ($this->_options['default']!== NULL) {	
			$opts[]=$this->_options['default'];	
		}
		
		$stmt = $db->query($sql);
		
		while ($row = $stmt->fetch()) {
			$opts[$row['a']] = $row['b'];
		}

		$this->setMultiOptions($opts);

	}
	
	public function setReadOnly() {
		$this->setAttrib('disabled', 'disabled');
	}
	
	public function init()
	{
		$this->setLabel(ucwords(str_replace("_"," ",$this->getName())));
		return parent::init();
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
	
	public function buildElement($action,$optionarray)
	{
		$this->_action = $action;
		$this->_options = $optionarray;
		
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
					
					break;
			}
		}
		
		return $this;
	}
	
}
?>