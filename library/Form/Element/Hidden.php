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

		/**
	* Stores extra form array elements that we might create
	*
	* @var mixed
	*/
	private $_newElements = array();

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

	public function init()
	{
		// Add a string trim filter
		$this->addFilters(array('StringTrim'));
		$this->setDecorators(array('ViewHelper'));
		return parent::init();
	}

	public function buildElement($action,$optionarray,$currentData=null)
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