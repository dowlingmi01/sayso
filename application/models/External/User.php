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
	public function getUser($user_password = NULL) {
		$user = new User();
		if( $this->user_id ) {
			$user->loadData( $this->user_id );
			if( $user_password ) {
				if( $user->password )
					$user->validatePassword($user_password);
				else {
					$user->setPlainTextPassword($user_password);
					$user->save();
				}
			}
		} else {
			// TODO: Handle race condition here.
			$user->save();
			$this->user_id = $user->getId();
			$this->save();
			
			if( $user_password ) {
				$user->setPlainTextPassword($user_password);
			}
			
			switch ($this->uuid_type) {
				case 'email' :
					$email = new User_Email();
					$email->email = $this->uuid;
					$user->setEmail($email);
					break;
				case 'username' :
					$user->username = $this->uuid;
					break;
				case 'integer' :
				case 'hash' :
				default :
					// do nothing for now
			}
			if( $this->email && !$user->email ) {
				$email = new User_Email();
				$email->email = $this->email;
				$user->setEmail($email);
			}
			$user->save();
		}
		return $user;
	}
}

