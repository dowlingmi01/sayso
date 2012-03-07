<?php


class External_User extends Record
{
	protected $_tableName = 'external_user';
	
	protected $_uniqueFields = array('uuid' => 0, 'starbar_id' => 0);
	
	public function exportData() {
		$fields = array(
			'user_id',
			'uuid',
			'uuid_type',
			'starbar_id',
			'install_token',
			'install_ip_address',
			'install_origination',
			'email',
			'username',
			'first_name',
			'last_name',
			'domain'
		);
		return array_intersect_key($this->getData(), array_flip($fields));
	}
	public function getUser() {
		$user = new User();
		if( $this->user_id ) {
			$user->setId( $this->user_id );
		} else {
			// TODO: Handle race condition here.
			$user->save();
			$this->user_id = $user->getId();
			$this->save();
			
			switch ($this->uuid_type) {
				case 'email' :
					$email = new User_Email();
					$email->email = $this->uuid;
					$user->setEmail($email);
					$user->save();
					break;
				case 'username' :
					$user->username = $this->uuid;
					$user->save();
					break;
				case 'integer' :
				case 'hash' :
				default :
					// do nothing for now
			}
		}
		return $user;
	}
}

