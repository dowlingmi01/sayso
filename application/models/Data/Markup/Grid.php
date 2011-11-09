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
		parent::__construct($options);
		$this->setExport(array());
	}
}