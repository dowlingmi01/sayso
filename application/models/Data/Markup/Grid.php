<?php
/**
 * @author alecksmart
 *
 * Custom interface to Bvb_Grid_Deploy_Table
 *
 */
class Data_Markup_Grid extends Bvb_Grid_Deploy_Table
{
	public function __construct($options = array())
	{
        $defaults = array
        (
            'grid'=>array
                (
                    'id'=>'list'
                )
        );
        $options = array_merge($defaults, $options);

		parent::__construct($options);

		$this->setExport(array());
        $this->setNoFilters(true);
		$this->setNoOrder(true);
	}

    
}