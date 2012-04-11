<?php

/**
 * Create a select list showing references from another table
 *
 * @author Peter Connolly
 */
class Form_Element_Fkey extends Zend_Form_Element_Select
{
	
	/**
	* Load a foreign-key association dropdown
	* 
	* @param mixed $valuearray
	* Valuearray has to include the following key/value pairs;
	*	'lookuptable' - The table which the foreign keys are being extracted from
	*	'lookupfield' - The key field (usually ID) in the table - will be used as the index
	*	'lookuplabel' - The field which you want to see in the drop-down list
	*	'default' - A text string which will sit at the top of the list until a choice is made
	* @author Peter Connolly
	*/
	public function setParams($valuearray)
	{
		
		$db = Zend_Registry::get('db');
        $sql = sprintf("SELECT %s as a, %s as b FROM %s ORDER BY %s ASC",$valuearray['lookupfield'],$valuearray['lookuplabel'],$valuearray['lookuptable'],$valuearray['lookuplabel']);
        
        if ($valuearray['default']!== NULL) {	
        	$options[]=$valuearray['default'];	
		}
		
		$stmt = $db->query($sql);
		
		while ($row = $stmt->fetch()) {
			$options[$row['a']] = $row['b'];
		}

        $this->setMultiOptions($options);
	}
	
    public function init()
    {
		 
        
        $this->setLabel(ucwords(str_replace("_"," ",$this->getName())));
     //   $this->setLabel('Starbar');
           //  ->addValidator('GreaterThan', false, array('min'=> 0,"messages" => array("notGreaterThan"=>"You need to choose a startbar")));
        return parent::init();
    }

}
?>