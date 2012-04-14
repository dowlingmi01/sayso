<?php

/**
 * Create a jQuery Date Picker for date fields
 *
 * @todo Need to add Time as well as date
 * @author Peter Connolly
 *
 */
class Form_Element_Date extends ZendX_JQuery_Form_Element_DatePicker
{
    public function init()
    {
		$this->setLabel(ucwords(str_replace("_"," ",$this->getName())));
        return parent::init();
    }

}
?>
