<?php

/**
 * Class to handle server table definitions
 *
 * @author Peter Connolly
 */
class Cms_TableCollection extends RecordCollection
{
    /**
     * Obtain a list of all tables in the sayso database
     *
     * @author Peter Connolly
     */

    /**
     * Store db instance for bvb_grid
     * @var type
     */
    protected $_db;


    public function init(){
	$this->_db = Zend_Registry::get("db");
	parent::init();
    }

    public function updateCmsTableList() {
	$sql = "SHOW TABLES";
	    $tables = Db_Pdo::fetchAll($sql);

	    foreach ($tables as $key => $value)
	    {
		//printf("<p>key [%s] value1 [%s]</p>",$key,$value['Tables_in_sayso']);

	    }
    }



    public function loadCmsTableList() {
	    $sql = "SHOW TABLES";
	    $tables = Db_Pdo::fetchAll($sql);

	    foreach ($tables as $key => $value)
	    {
	//	printf("<p>key [%s] value1 [%s]</p>",$key,$value['Tables_in_sayso']);

	    }


	    /*if ($tables) {
		    $this->build($tables, new Cms_TableList());
	    }*/

	    //$source = new Bvb_Grid_Source_Zend_Table(new Survey());
//$grid->setSource($source);
/*

$db = Zend_Registry::get('db');

printf("<p>Started</p>");
	    $grid = new Bvb_Grid_Deploy_Table(array($db,'Petes table list','temp/dir',array('save','download')));
	    $grid->query($db->select()->from('survey'));
	    $this->view->grid = $grid->deploy();
*/
printf("<p>Finished!</p>");
}
}
