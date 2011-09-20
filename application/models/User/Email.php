<?php

class User_Email extends Record implements Titled
{
    protected $_tableName = 'user_email';
    
    protected $_uniqueFields = array('user_id' => 0, 'email' => '');
    
    public function getTitle() {
        return $this->email;
    }
}

