<?php
/**
 * Session model
 *
 * Handles session actions
 */
class User_Session extends Record
{
	/**
	 *the name of the table
	 *
	 * @var string
	 */
	protected $_tableName = 'user_session';

	/**
	 * Unique fields for this table
	 *
	 * @var array
	 */
	protected $_uniqueFields = array('session_key' => '');

	/**
	 * Sets up a new session
	 *
	 * Upon successful login, this function clears any existing
	 * session with the same user, ip, and browser then sets
	 * a new one.
	 *
	 * @param string $userId
	 */
	public function setSession($userId)
	{
		$browser = new Browser();
		$browser->processAgentString();
		if (!$browser->id)
			throw new Exception("Could not save browser data.");
		$this->browser_id = $browser->id;

		//expire existing sessions for this user and ip
		Db_Pdo::execute('UPDATE '. $this->_tableName .' SET expired = now() WHERE user_id = ? AND ip = INET_ATON(?) AND browser_id = ?', $userId, $_SERVER["REMOTE_ADDR"], $browser->id);

		//add entry to session table
		$this->user_id = $userId;

		$this->ip = new Zend_Db_Expr("INET_ATON('" . $_SERVER["REMOTE_ADDR"] . "')");

		$sessionKey = $this->_getRandomSessionKey();
		$this->session_key = $sessionKey;

		$this->save();
	}

	/**
	 * Recursive function to ensure the session key is unique
	 * in the database
	 *
	 * @return string
	 * @todo there is a small chance of entering a loop
	 * only in that there is no break. it should always be
	 * able to find a unique value at some point.....
	 */
	private function _getRandomSessionKey()
	{
		$key = Helper::getRandomToken();
		$sessionKey = Db_Pdo::fetch('SELECT session_key FROM '.$this->_tableName.' WHERE session_key = ?', $key);
		if (empty($sessionKey))
			return $key;
		else
			return $this->getRandomSessionKey();
	}

	static public function validate($session_id, $session_key) {
		$session = Db_Pdo::fetch('SELECT * FROM user_session WHERE id = ?', $session_id);
		if (!empty($session))
			return $session_key == $session["session_key"] ? $session["user_id"] : FALSE;
		else
			return;
	}

	/**
	 * Checks to see if a session is active.
	 *
	 * If the session time has expired, allow one more
	 * transaction on it and create a new session.
	 * Reset the session timer on each call.
	 *
	 * Note - This function is deliberately made static
	 * to avoid utilizing the Record class that this class
	 * extends in order to maintain the most optimal
	 * performance. Queries have been hand written to
	 * optimize this function.This function is called with EVERY
	 * call to the API so it needs to maintain performance.
	 *
	 * @return string|bool
	 */
	public static function checkSession($sessionId)
	{
		$session =  Db_Pdo::fetch(
"SELECT
	user_session.*,
	IF(CURRENT_TIMESTAMP - created > 86400 OR expired IS NOT NULL, 1, 0) AS is_expired,
	IF(user_session.expired IS NOT NULL AND CURRENT_TIMESTAMP - user_session.expired  < 600, 1, 0) AS recently_expired
FROM user_session
WHERE id = ?", $sessionId);
		//check session time
		if (!empty($session))
		{
			if ($session["is_expired"] == "0")
			{
				return TRUE;
			}
			if ($session["recently_expired"] == "1")
			{
				$newSession = new self;
				$newSession->loadData($session["new_user_session_id"]);
				return array("new_user_session_id" => $session["new_user_session_id"], "new_session_key" => $newSession->session_key);
				//TODO: perhaps add a catch for no new session id here
			} else {
				$newSession = new self;
				$newSession->setSession($session["user_id"]);
				Db_Pdo::execute('UPDATE user_session SET new_user_session_id = ? WHERE id = ?', $newSession->id, $sessionId);
				return $newSession->id;
			}
		} else
			return;
	}

	/**
	 * Renders a session expired
	 *
	 * @param int $sessionId
	 */
	public static function logout($sessionId)
	{
		Db_Pdo::execute('UPDATE user_session SET expired = now() WHERE id = ?', $sessionId);
	}
}