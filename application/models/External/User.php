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
}

