<?php

class User_Email extends Record implements Titled
{
    protected $_tableName = 'user_email';
    
    public function getTitle() {
        return $this->email;
    }
}

