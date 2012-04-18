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
		$this->setRecordsPerPage(11);
		$this->setPaginationInterval(array(10=>10,20=>20,50=>50,100=>100));
	}


}
