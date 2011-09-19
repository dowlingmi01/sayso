<?php

class Starbar_UserMap extends Record {
    
    protected $_tableName = 'starbar_user_map';
    
    protected $_uniqueFields = array('user_id' => 0, 'starbar_id' => 0);
}