<?php
/**
* Skeleton for a Select form element
*
* @author Peter Connolly
*/
class Form_Element_Select extends Zend_Form_Element_Select {
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
		// Set the default title
		$this->setLabel(ucwords(str_replace("_"," ",$this->getName())));
		return parent::init();
	}

	public function setReadOnly()
	{
		$this->setAttrib('disabled', 'disabled');
	}

}
?>