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

    public function setAdminRoles(array $roles)
    {
        
    }

    /**
     * Save user
     * @param AdminUser $adminUser
     * @param array $values
     */
    public static function saveUserFromValues(AdminUser $adminUser, array $values, $action = 'create')
    {
        if($adminUser->getId() > 0 && self::findCountByEmail($values['txtLogin'], $adminUser->getId()))
        {
           throw new Exception('This email is already used!');
        }
        elseif(!$adminUser->getId() && self::findCountByEmail($values['txtLogin']))
        {
           throw new Exception('This email is already used!');
        }
        $adminUser->first_name  = $values['txtFirstName'];
        $adminUser->last_name   = $values['txtLastName'];
        $adminUser->email       = $values['txtLogin'];
        if($values['passwPassword'] > '')
        {
            $adminUser->password = md5($values['passwPassword']);
        }
        $adminUser->save();
    }

    /**
     * Check if email is unique
     * @param type $email
     * @param type $excludeId
     */
    public static function findCountByEmail($email, $excludeId = 0)
    {
        $query  = "SELECT COUNT(*) AS cnt FROM admin_user WHERE email = ?";
        $params = array($email);
        if($excludeId)
        {
            $query      .= " AND id != ? ";
            $params[]   = $excludeId;
        }
        $results = call_user_func_array(array('Db_Pdo', 'fetch'), array_merge(array($query), $params));        
        return intval($results['cnt']);
    }

}