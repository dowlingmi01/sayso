<?php
/**
 * Class for handling admiistrative users
 * @author alecksmart
 */
class AdminUser extends Record
{

    protected $_tableName = 'user';

    public function setPassword($password)
    {
        $this->_password = md5($password);
    }

    public static function getByEmail($email)
    {
        return null;
    }

}
