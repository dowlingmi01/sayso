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
    
    /**
     * Create Gamer by user id / starbar id
     * 
     * @param int $userId
     * @param int $starbarId
     * @return Gamer
     */
    public static function create ($userId, $starbarId) {
        $gamer = new self;
        $gamer->loadDataByUniqueFields(array('user_id' => (int) $userId, 'starbar_id' => (int) $starbarId));
        if (!$gamer->hasId()) {
            // new gaming user, so generate a unique gaming ID and save
            $gamer->generateUniqueId();
            $gamer->save();
            $gamer->reload();
        }
        return $gamer;
    }
}