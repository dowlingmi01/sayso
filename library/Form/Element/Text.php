<?php

/**
 * Create a form text box
 *
 * @author Peter Connolly
 */
class Form_Element_Text extends Zend_Form_Element_Text
{
/**
     * Help text string
     * @var string
     */
    protected $_helpText;

    public function init()
    {
	// Set the default title
        $this->setLabel(ucwords(str_replace("_"," ",$this->getName())));
	// Add a string trim filter
	$this->addFilters(array('StringTrim'));

        return parent::init();
    }

    /**
     * Set the help text for the current element
     *
     * @author Peter Connolly
     * @param type $value
     * @return \Form_Element_Text
     */
    public function setHelpText($value)
    {
	$this->_helpText = $value;
	$this->setAttrib("Title", $this->_helpText);

        return $this;
    }

    /**
     * Get the help text for the current element
     *
     * @author Peter Connolly
     * @return string Help Text. Null if not set
     */
    public function getHelpText()
    {
	return $this->_helpText;
    }

    /**
     * Set this element as readonly. Note that we also set the class of the element to .readonly
     *
     * * @author Peter Connolly
     * @return \Form_Element_Text
     */
    public function setReadonly()
    {

	$this->setAttrib("readonly","");
	$this->setAttrib("class","readonly");

        return $this;
    }

}
?>
