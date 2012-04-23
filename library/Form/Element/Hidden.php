<?php

/**
* Create a hidden field on a form
*
* @author Peter Connolly
*/
class Form_Element_Hidden extends Zend_Form_Element_Hidden
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
	* Help text string
	* @var string
	*/
	protected $_helpText;

	public function init()
	{
		// Add a string trim filter
		$this->addFilters(array('StringTrim'));
		$this->setDecorators(array('ViewHelper'));
		return parent::init();
	}

	public function buildElement($action,$optionarray)
	{
		$this->_action = $action;
		$this->_options = $optionarray;
		
		// Are we going to display this element?
		if (in_array($action,$this->_options['displaywhen'])) {
			
			// We're going to display this element. Now look to see what we do with it
			switch ($action) {
				
				case "add" :
					
					break;
					
				case "detail" :
				case "delete" :

					break;
				
				case "edit":
				
					break;
			}
		}
		
		return $this;
	}

}
?>