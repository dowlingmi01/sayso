<?php
/**
 * @author alecksmart
 */

class AdminUser_AdminRole extends Record
{
    protected $_tableName       = 'admin_user_admin_role';

    protected $_idKey           = null;

    protected $_uniqueFields    = array('admin_user_id' => 0, 'admin_role_id' => 0);

    public function init()
    {
        parent::init();
        $this->setZendDbTableOptions(array(Zend_Db_Table_Abstract::PRIMARY => NULL));
    }
}

