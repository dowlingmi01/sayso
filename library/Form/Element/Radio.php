<?php
/**
* Skeleton for a Radio form element
*
* @author Peter Connolly
*/
class Form_Element_Radio extends Zend_Form_Element_Radio {
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