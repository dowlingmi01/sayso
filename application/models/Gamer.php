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
		if (!$userId || !$starbarId) {
			throw new Api_Exception(Api_Error::create(Api_Error::GAMING_ERROR, 'Gamer::create() requires user id (' . $userId . ') and starbar id (' . $starbarId . ')'));
		}
		$gamer = new static;
		$gamer->loadDataByUniqueFields(array('user_id' => (int) $userId, 'starbar_id' => (int) $starbarId));
		if (!$gamer->hasId()) {
			// new gaming user, so generate a unique gaming ID and save
			$gamer->generateUniqueId();
			$gamer->save();
			$gamer->reload();
		}
		return $gamer;
	}
	
	/**
	 * Reset the current gaming user and return the new gaming user
	 * 
	 * @param int $userId
	 * @param int $userKey
	 * @param int $starbarId
	 * @return Gamer
	 */
	public static function reset ($userId, $userKey, $starbarId) {
		$gamer = new static;
		$gamer->loadDataByUniqueFields(array('user_id' => (int) $userId, 'starbar_id' => (int) $starbarId));
		$gamer->delete();
		
		$newGamer = self::create($userId, $starbarId);
		return $newGamer;
	}
}
