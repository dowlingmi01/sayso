<?php
/**
 * Login model
 *
 * Handles login actions.
 *
 */

class User_Login {

	/**
	 * Performs login actions when an email and password are
	 *  submitted
	 *  - Checks if ip is banned
	 *  - Checks for failed login attempts
	 *  - Cheks to see if email and password match
	 *  - Adds strikes if they don't
	 *  - Creates a new session
	 *  - Returns User and Session objects wrapped in an array
	 *
	 *
	 * @param string $email
	 * @param string $password
	 * @return array Contains \User_Session object
	 */
	public static function loginWithEmail($email, $password)
	{
		//check ip ban
		if (User::isIpBanned())
			return;

		//check login attempts
		$loginStrikes = self::_getLoginStrikes($email);
		if (!isset($loginStrikes))
			return;

		//check ip attempts
		$ipStrikes = self::_getIpStrikes();
		if (!isset($ipStrikes))
			return;

		// attempt getting the user
		$userEmail = Db_Pdo::fetch('SELECT id FROM user_email WHERE email = ?', $email);
		if (empty($userEmail))
			return self::_addStrikes($email);

		$userRow = Db_Pdo::fetch('SELECT * FROM user WHERE id = ?', $userEmail["id"]);
		// no user found
		if (empty($userRow))
			return self::_addStrikes($email);

		if( $userRow['status'] != 'active' )
			return;

		// calculate the password hash using the retrieved password salt
		$passwordHash = md5(md5($password) . $userRow['password_salt']);

		// compare provided password to saved password
		if ($userRow['password'] !== $passwordHash) {
			//add strike
			self::_addStrikes($email);
			return;
		}

		// all is good
		$session = new User_Session();
		$session->setSession($userRow["id"]);

		$result = array("session" => $session);

		return $result;
	}

	public static function loginMachinimaReload($email, $digest)
	{
		// attempt getting the user
		$userEmail = Db_Pdo::fetch('SELECT id FROM user_email WHERE email = ?', $email);
		if (empty($userEmail))
			return;

		$userRow = Db_Pdo::fetch('SELECT * FROM user WHERE id = ?', $userEmail["id"]);
		// no user found
		if (empty($userRow))
			return;

		if( $userRow['status'] != 'active' )
			return;

		MachinimaReload::verifyEmail($email, $digest);

		// all is good
		$session = new User_Session();
		$session->setSession($userRow["id"]);

		$result = array("session" => $session);

		return $result;
	}

	/**
	 * Performs login actions with legacy user_key for transition period
	 *  - Checks if ip is banned
	 *  - Checks if the user_key exists
	 *  - Adds strike to the ip if it doesn't
	 *  - Creates a new session
	 *  - Returns User and Session objects wrapped in an array
	 *
	 *
	 * @param string $user_key
	 * @return array Contains \User_Session object
	 */
	public static function loginWithLegacyKey($user_key)
	{
		//check ip ban
		if (User::isIpBanned())
			return;

		//check ip attempts
		$ipStrikes = self::_getIpStrikes();
		if (!isset($ipStrikes))
			return;

		$user_id = User_Key::validate($user_key);

		if (!$user_id)
			return self::_addStrikes();

		// all is good
		$session = new User_Session();
		$session->setSession($user_id);

		$result = array("session" => $session);

		return $result;
	}

	/**
	 * Checks to see if there have been too many failed logins with a username
	 *
	 * @param string $user
	 * @return bool|int
	 */
	static private function _getLoginStrikes($user)
	{
		//remove old ones
		Db_Pdo::execute('DELETE FROM login_strikes_user WHERE created < now()-INTERVAL 5 MINUTE AND username = ?', $user);
		//count whats left
		$strikes = Db_Pdo::fetch('SELECT count(username) AS count FROM login_strikes_user WHERE username = ?', $user);
		if ($strikes["count"] >= 5)
			return;
		else
			return (int)$strikes["count"];
	}

	/**
	 * Checks to see if there have been too many failed logins with the requesting IP
	 *
	 * @return bool|int
	 */
	static private function _getIpStrikes()
	{
		//remove old ones
		Db_Pdo::execute('DELETE FROM login_strikes_ip WHERE created < now()-INTERVAL 5 MINUTE AND ip = INET_ATON(?)', $_SERVER["REMOTE_ADDR"]);
		//count whats left
		$strikes = Db_Pdo::fetch('SELECT count(ip) AS count FROM login_strikes_ip WHERE ip = INET_ATON(?)', $_SERVER["REMOTE_ADDR"]);
		if ($strikes["count"] >= 5)
			return;
		else
			return (int)$strikes["count"];
	}

	/**
	 * Adds strikes if a login attempt failed.
	 *
	 * @param type $username
	 */
	static private function _addStrikes($username = NULL)
	{
		if( $username )
			Db_Pdo::execute("INSERT INTO login_strikes_user (username) VALUES (?)", $username);
		Db_Pdo::execute('INSERT INTO login_strikes_ip (ip) VALUES (INET_ATON(?))', $_SERVER["REMOTE_ADDR"]);
	}
}
?>
