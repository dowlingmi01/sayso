<?php

class User_Key extends Record
{
	const ORIGIN_INSTALL = 100
	    , ORIGIN_USER_STATE = 101;
	
	protected $_tableName = 'user_key';
	
	protected $_uniqueFields = array('token' => '');

	static public function getRandomToken() {
		$s = "";
		$characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		for ($i = 0; $i < 32; $i++) {
			$s .= $characters[mt_rand() % strlen($characters)];
		}
		return $s;
	}
	
	static public function validate( $token, $user_id = null ) {
		$user_key = new User_Key();
		$user_key->loadDataByUniqueFields(array('token'=>$token));
		if( $user_key->hasId() ) {
			if( !$user_id || $user_id == $user_key->user_id )
				return $user_key->user_id;
		}
		return null;
	}
}
