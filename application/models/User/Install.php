<?php


class User_Install extends Record
{
	protected $_tableName = 'user_install';
	
	protected $_uniqueFields = array('token' => '');
	
}

