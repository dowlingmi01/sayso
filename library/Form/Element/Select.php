<?php
/**
 * Skeleton for a Select form element
 *
 * @author Peter Connolly
 */
class Form_Element_Select extends Zend_Form_Element_Select {
    public function init()
    {
    	// Set the default title
        $this->setLabel(ucwords(str_replace("_"," ",$this->getName())));
		return parent::init();
    }

}
?>