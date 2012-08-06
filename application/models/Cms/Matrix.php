<?php
/**
 * @author Peter Connolly
 *
 * Custom interface to Bvb_Grid_Deploy_Table for CMS grid
 *
 */
class Cms_Matrix extends Bvb_Grid_Deploy_Table
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

		$this->setImagesUrl('/images/');
		$this->setExport(array());
		$this->setNoFilters(false);
		$this->setAlwaysShowOrderArrows(false);
		$this->setNoOrder(false);
		$this->setUseKeyEventsOnFilters(true);
		$this->setRecordsPerPage(100);
		$this->setPaginationInterval(array(25=>25,50=>50,75=>75,100=>100,150=>150));
	}


}
