<?php

class User extends Record
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
     * Plain text password as provided by user
     * - stored as a protected property so we
     *   we don't confuse this with the md5'd
     *   (and salted) 'password' field
     *   
     * @var string
     */
    protected $_plainTextPassword;
    
    public function setEmail (User_Email $email) {
        $this->_email = $email;
    }
    
    public function getEmail () {
        if (!$this->_email) {
            // @todo look it up
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
        // commit all changes
        $this->commitTransaction();
    }
    
    public function exportProperties($parentObject = null) {
        $props = array(
            '_email' => $this->_email,
            '_preferences' => $this->_generalPreference,
            '_survey_types' => $this->_surveyTypes
        );
        return array_merge(parent::exportProperties($parentObject), $props);
    }
}

