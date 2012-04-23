<?php

/**
* Create a form text box which allows only positive integer numbers
*
* @author Peter Connolly
*/
class Form_Element_Number extends Form_Element_Text
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

	public function init()
	{
	// Set the default title
		$this->setLabel(ucwords(str_replace("_"," ",$this->getName())));
		$this->addValidator('Int', true);
		$this->addValidator('GreaterThan',false, -1); // Number must be 0 or more
		$this->setErrorMessages(array('Must be a positive integer, or null'));
		return parent::init();
	}

	/**
	* Set this element as readonly. Note that we also set the class of the element to .readonly
	*
	* @author Peter Connolly
	* @return \Form_Element_Text
	*/
	public function setReadOnly()
	{

		$this->setAttrib("readonly","readonly");
		$this->setAttrib("class","readonly");
		$this->setAttrib("disabled","disabled");
		return $this;
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
