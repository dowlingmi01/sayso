<?php
/**
 * Class for handling administrative user roles
 *
 * @author alecksmart
 */
class AdminUser extends Record
{

    protected $_tableName = 'admin_user';

    /**
     * Prepare for writing to database 
     * 
     * @param string $password 
     */
    public function setPassword($password)
    {
        $this->password = md5($password);
    }

    /**
     * Get instance according to unique email address
     * @param string $identity email address
     */
    public static function getByEmail($email)
    {
        return null;
    }

    /**
     * Perform login
     *
     * @param type $email
     * @param type $password
     */
    public static function login($email, $password)
    {
        
    }

    /**
     * Perform logout
     */
    public static function logout()
    {
        
    }

}
