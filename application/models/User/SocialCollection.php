<?php


class User_SocialCollection extends RecordCollection
{
    public function loadForUser ($userId)
    {
        $userSocials = Db_Pdo::fetchAll("SELECT * FROM user_social WHERE user_id = ?", $userId);
		if ($userSocials) {
        	$this->build($userSocials, new User_Social());
		}
    }
}
