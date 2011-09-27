<?php


class User_Social extends Record
{
    protected $_tableName = 'user_social';
    
    /* provider = 'facebook' or 'twitter' */
    public function loadByUserIdAndProvider($userId, $provider) {
    	$userSocialFetch = null;

    	if ($userId && $provider)
        	$userSocialFetch = Db_Pdo::fetch("SELECT * FROM user_social WHERE provider = ? AND user_id = ?", $provider, $userId);

		if ($userSocialFetch)
        	$this->build($userSocialFetch);
	}
}

