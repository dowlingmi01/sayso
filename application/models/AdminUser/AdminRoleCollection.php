<?php


class AdminUser_AdminRoleCollection extends RecordCollection
{
    public function loadForUser($userId)
    {
        $entities = Db_Pdo::fetchAll(
            "SELECT ar.* FROM admin_user_admin_role u, admin_role ar WHERE u.admin_user_id = ? AND u.admin_role_id = ar.id",
            $userId
        );
		if ($entities) {
        	$this->build($entities, new AdminRole());
		}
    }
}
