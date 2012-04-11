<?php

/**
 * Create a select list showing all available starbars
 *
 * @author Peter Connolly
 */
class Form_Element_Starbar extends Zend_Form_Element_Select
{
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