<?php

/**
 * Create a select list showing all available starbars
 *
 * @author Peter Connolly
 */
class Form_Element_Starbar extends Zend_Form_Element_Select
{
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

        $db = Zend_Registry::get('db');
        $options = $db->fetchPairs('SELECT id, label FROM starbar ORDER BY label ASC');
        array_unshift($options,"-- Please select a starbar --");
        $this->setMultiOptions($options);
        $this->setLabel('Starbar')
             ->addValidator('GreaterThan', false, array('min'=> 0,"messages" => array("notGreaterThan"=>"You need to choose a startbar")));
        return parent::init();
    }

}
?>