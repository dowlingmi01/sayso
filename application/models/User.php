<?php

class User extends Record implements Titled
{
	protected $_tableName = 'user';

	/**
	 * @var User_Email
	 */
	protected $_email;

	/**
	 * @var Preference_General
	 */
	protected $_generalPreference;

	/**
	 * @var Preference_SurveyTypeCollection
	 */
	protected $_surveyTypes;

	/**
	 * @var User_Social
	 */
	protected $_userSocials;

	/**
	 * Plain text password as provided by user
	 * - stored as a protected property so we
	 *   we don't confuse this with the md5'd
	 *   (and salted) 'password' field
	 *
	 * @var string
	 */
	protected $_plainTextPassword;

	public function getTitle ($starbar_id = null)
	{
		if ($this->username) return $this->username;
		else if( $starbar_id ) return Game_Transaction::getUserLevelName($this->id, $starbar_id);
		else return 'User';
	}

	public function setEmail (User_Email $email) {
		$this->_email = $email;
	}

	public function getEmail () {
		if (!$this->_email) {
			if ($this->primary_email_id) {
				$this->_email = new User_Email();
				$this->_email->loadData($this->primary_email_id);
			} else {
				$this->_email = new NullObject('User_Email');
			}
		}
		return $this->_email;
	}

	public function setPreference (Preference_General $pref) {
		$this->_generalPreference = $pref;
	}

	public function getPreference () {
		if (!$this->_generalPreference) {
			// @todo look it up
		}
		return $this->_generalPreference;
	}

	public function setSurveyTypes (Preference_SurveyTypeCollection $surveyTypes) {
		$this->_surveyTypes = $surveyTypes;
	}

	public function getSurveyTypes () {
		if (!$this->_surveyTypes) {
			// @todo look it up
		}
		return $this->_surveyTypes;
	}

	public function setUserSocials (User_SocialCollection $userSocials) {
		$this->_userSocials = $userSocials;
	}

	public function getUserSocials () {
		if (!$this->_userSocials) {
			$userSocials = new User_SocialCollection();
			$userSocials->loadForUser($this->getId());
			$this->_userSocials = $userSocials;
		}
		return $this->_userSocials;
	}

	public function loadUserSocials () {
		$userSocials = new User_SocialCollection();
		$userSocials->loadForUser($this->getId());
		$this->_userSocials = $userSocials;
	}

	/**
	 * Set the plain text password as provided by the user
	 * for instance, on registration or for password change
	 *
	 * @param string $password
	 */
	public function setPlainTextPassword ($password)
	{
		$this->_plainTextPassword = $password;
	}

	/**
	 * Override save() to handle
	 * - generating new password hash (as necessary)
	 * - saving aggregate records (e.g. email)
	 * - updating primary_*_id columns
	 *
	 * @see Record::save()
	 */
	public function save () {

	if ($this->_plainTextPassword) { // new user password OR password change

			// ensure password salt and the password are always generated AND
			// inserted at the same time, so password can be rebuilt for login
			$this->password_salt = createRandomCode(3);
			$this->password = md5(md5($this->_plainTextPassword) . $this->password_salt);
		}
		$this->beginTransaction();
		parent::save();
		if ($this->_email) {
			$this->_email->user_id = $this->getId();
			$this->_email->save();

			// check if this is a test email address, if so, mark the user as such (unless they already are)
			if ($this->type != 'test' && User_Email::isTestEmail($this->_email->email))
				$this->type = 'test';

			// update primary email address & re-save the User
			$this->primary_email_id = $this->_email->getId();
			parent::save();
		}
		if ($this->_generalPreference) {
			$this->_generalPreference->user_id = $this->getId();
			$this->_generalPreference->save();
		}
		if ($this->_surveyTypes) {
			// first remove all existing survey types
			Db_Pdo::execute('DELETE FROM preference_survey_type WHERE user_id = ?', $this->getId());
			// second save all survey types the user wants
			foreach ($this->_surveyTypes as $surveyType) {
				/* @var $surveyType PreferenceSurveyType */
				$surveyType->user_id = $this->getId();
				$surveyType->save();
			}
		}
		if ($this->_userSocials) {
			foreach ($this->_userSocials as $userSocial) {
				$userSocial->user_id = $this->getId();
				$userSocial->save();
			}
		}
		// commit all changes
		$this->commitTransaction();
	}
	public function validatePassword($password) {
		$passwordHash = md5(md5($password) . $this->password_salt);
		if ($this->password !== $passwordHash) {
			throw new Api_UserException(Api_Error::create(260, "Incorrect password"));
		}
	}
	public function exportData() {
		$fields = array(
			'username',
			'first_name',
			'last_name',
			'birthdate',
			'timezone',
			'type'
		);
		return array_intersect_key($this->getData(), array_flip($fields));
	}

	public function exportProperties($parentObject = null) {
		$props = array(
			'_email' => $this->getEmail()->getTitle(),
			'_preferences' => $this->_generalPreference,
			'_survey_types' => $this->_surveyTypes,
			'_user_socials' => $this->_userSocials
		);
		if ($this->_key)
		{
			$props['_key'] = $this->_key;
		}
		return array_merge(parent::exportProperties($parentObject), $props);
	}

	public function getPrimaryAddress() {
		$userAddress = new User_Address();
		if ($this->primary_address_id) {
			$userAddress->loadData($this->primary_address_id);
		}
		return $userAddress;
	}

	/**
	 * Checks to see if there is an active ban on the ip making the request.
	 */
	public static function isIpBanned()
	{
		$banRow = Db_Pdo::fetch('SELECT * FROM login_ban_ip WHERE ip = INET_ATON(?)', $_SERVER["REMOTE_ADDR"]);
		if (empty($banRow))
			return;
	}

	public static function create( $email, $pw, $starbarId) {
		$names = self::validateEmailForStarbar($email, $starbarId);

		$userEmail = new User_Email();

		$userEmail->loadDataByUniqueFields(array('email'=>$email));
		$user = new User();
		if ($userEmail->id) {
			$user->loadData( $userEmail->user_id );
			if( $user->password )
				throw new Exception("EMAIL_ADDRESS_ALREADY_REGISTERED");
		} else {
			$user->originating_starbar_id = $starbarId;
			$userEmail->email = $email;
			$user->setEmail($userEmail);
		}
		if(array_key_exists('last_name', $names))
			$user->last_name = $names['last_name'];
		if(array_key_exists('first_name', $names))
			$user->first_name = $names['first_name'];

		$user->setPlainTextPassword($pw);
		$user->save();

		if (!$user->id)
			throw new Exception('Failed to save user.');

		$starbarUserMap = new Starbar_UserMap();
		$starbarUserMap->user_id = $user->id;
		$starbarUserMap->starbar_id = $starbarId;
		$starbarUserMap->active = 1;
		$starbarUserMap->onboarded = 1;
		$starbarUserMap->save();
		$insertStatus = $starbarUserMap->wasInserted();
		if($insertStatus)
			Game_Transaction::run($user->id, Economy::getIdforStarbar($starbarId), 'STARBAR_OPT_IN');

		$userState = new User_State();
		$userState->user_id = $user->id;
		$userState->starbar_id = $starbarId;
		$userState->visibility = "open";
		$userState->save();

		return $user->id;
	}

	public static function validateEmailForStarbar($email, $starbarId) {
		if( $starbarId != 7 || User_Email::isTestEmail($email) )
			return array();
		$sql = 'SELECT first_name, last_name FROM starbar_valid_email WHERE starbar_id = ? AND email = ?';
		$result = Db_Pdo::fetch($sql, $starbarId, $email);
		if(!$result)
			throw new Exception('user_does_not_have_access_to_starbar');
		return $result;
	}
	public static function validateUserIdForStarbar($userId, $starbarId) {
		if( $starbarId != 7 )
			return;
		$user = new User();
		$user->loadData($userId);
		if($user->type == 'test')
			return;
		self::validateEmailForStarbar($user->getEmail()->email, $starbarId);
	}

}

