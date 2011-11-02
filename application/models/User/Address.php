<?php

class User_Address extends Record
{
	protected $_tableName = 'user_address';

	public function loadOrPrepareForUser ($userId) {
		$this->loadDataByUniqueFields(array('user_id' => $userId));
		if (!$this->id) {
			$this->user_id = $userId;
		}
	}
}

