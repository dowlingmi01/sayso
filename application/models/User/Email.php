<?php

class User_Email extends Record implements Titled
{
	protected $_tableName = 'user_email';

	protected $_uniqueFields = array('user_id' => 0, 'email' => '');

	public function getTitle() {
		return $this->email;
	}

	/**
	* returns an array of all user ID's for a given email address
	*
	*
	* @param mixed $email
	*/
	public function getUserID($email)
	{
		$idarray = array();
		$sql = sprintf("select * from user_gaming g left join user_email e on g.user_id = e.user_id where email = '%s'",$email);
		$userID = Db_Pdo::fetchAll($sql);
		if ($userID) {
		return $userID;
		} else {
			return null;
		}
	}
}

