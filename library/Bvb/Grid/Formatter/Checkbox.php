<?php

/**
 * Format a checkbox based on the input value
 *
 * 1 = Checked
 * 0 = Unchecked
 *
 * @author Peter Connolly
 */
class Bvb_Grid_Formatter_Checkbox implements Bvb_Grid_Formatter_FormatterInterface {

    /**
     * Constructor
     * @param array $options
     */
    public function __construct($options = array())
    {

    }

    /**
     * Formats a given value
     * @see library/Bvb/ Grid/Formatter/Bvb_Grid_Formatter_FormatterInterface::format()
     */
    public function format($value)
    {
       // $translate = Bvb_Grid_Translator::getInstance();
	return  ($value=="1") ? "checked='checked'": "";

    }

}