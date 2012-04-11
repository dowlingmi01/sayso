<?php

/**
 * Create a form text box
 *
 * @author Peter Connolly
 */
class Form_Element_Hidden extends Zend_Form_Element_Hidden
{
/**
     * Help text string
     * @var string
     */
    protected $_helpText;

    public function init()
    {
		// Add a string trim filter
		$this->addFilters(array('StringTrim'));
 		//$this->setConfig(new Zend_Config(array('disableLoadDefaultDecorators' => true)));
 		$this->setDecorators(array('ViewHelper'));
        return parent::init();
    }


}
?>