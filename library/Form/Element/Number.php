<?php

/**
 * Create a form text box which allows only positive integer numbers
 *
 * @author Peter Connolly
 */
class Form_Element_Number extends Form_Element_Text
{

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
