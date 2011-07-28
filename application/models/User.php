<?php

class User extends Record
{
    protected $_tableName = 'user';
    
    /**
     * @var Email
     */
    protected $_email;
    
    /**
     * Plain text password as provided by user
     * - stored as a protected property so we
     *   we don't confuse this with the md5'd
     *   (and salted) 'password' field
     *   
     * @var string
     */
    protected $_plainTextPassword;
    
    public function setEmail (Email $email) {
        $this->_email = $email;
    }
    
    public function getEmail () {
        if (!$this->_email) {
            // look it up
        }
        return $this->_email;
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
        // commit all changes
        $this->commitTransaction();
    }
}

