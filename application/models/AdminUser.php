<?php
/**
 * Class for handling administrative user roles
 *
 * @author alecksmart
 */
class AdminUser extends Record
{

    protected $_tableName = 'admin_user';

    private $_roles;

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
     * @return AdminUser
     */
    public static function getByEmail($email)
    {
        $user = new AdminUser();
		$user->loadDataByUniqueFields(array('email' => $email));
        return $user->id > 0 ? $user : null;
    }

    public function getAdminRoles()
    {
        if (is_null($this->_roles)) {
        	$_roles = new AdminUser_AdminRoleCollection();
        	$_roles->loadForUser($this->getId());
            $this->_roles = $_roles;
        }
        return $this->_roles;
    }

}