<?php

/**
* Create a jQuery Date Picker for date fields
*
* @author Peter Connolly
*
*/
class Form_Element_Date extends ZendX_JQuery_Form_Element_DatePicker
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
		$this->setLabel(ucwords(str_replace("_"," ",$this->getName())));
		return parent::init();
	}

	public function setReadOnly() 
	{
		$this->setAttrib("readonly","");
		$this->setAttrib("class","readonly");
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
	
		$this->setJQueryParams(array('dateFormat' => 'yy-mm-dd'));
		$this->_setHelp();
		
		// Are we going to display this element?
		if (in_array($action,$this->_options['displaywhen'])) {
			
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
