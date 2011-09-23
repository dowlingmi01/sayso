<?php


class External_UserInstall extends Record
{
    protected $_tableName = 'external_user_install';
    
    protected $_uniqueFields = array('ip_address' => '', 'user_agent' => '', 'begin_time' => '');
    
}

