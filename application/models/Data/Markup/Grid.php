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

    public function setCollectionSource($items, $columns)
	{
		$source = array();
        if(empty($items))
        {
            $columns    = array('Warning');
            $source     = array(array('Warning'=>'This list is empty...'));
        }
        else
        {
            // format any loopable item to array...
            foreach ($items as $item)
            {
                $source[] = $item;
            }
        }
        $this->setSource(new Bvb_Grid_Source_Array($source, $columns));
	}
}