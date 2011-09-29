<?php 

class Gamer extends Gaming_User {
    /**
     * Since this extends legacy classes which set this field
     * we need to nullify it here, so the sayso db is used 
     */
    protected $_dbName = null;
    
    /**
     * @var string
     */
    protected $_tableName = 'user_gaming';
    
    
}