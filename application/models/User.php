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
    
    /**
     * The unique session id for this user
     * 
     * @var string
     */
    protected $_key;
    
    public function getTitle ()
    {
    	if ($this->username) return $this->username;
    	return Game_Starbar::getInstance()->getGamer()->getHighestLevel()->title;
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
     * Set the unique session id for this user
     * 
     * @param $key
     */
    public function setKey ($key) {
        $this->_key = $key;
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
    
    public function exportData() {
        $fields = array(
            'username',
            'first_name',
            'last_name',
            'birthdate',
            'timezone'
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
	
	public static function getHash ($userId) {
        return md5('User ' . $userId . ' rocks!');
	}
}

