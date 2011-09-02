<?php

require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Api_UserController extends Api_GlobalController
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        
        return $this->_resultType(new Object(array('foo' => 'bar')));
    }

    public function registerAction () 
    {
        $this->_createMissingParameters(array('email_frequency_id', 'poll_frequency_id', 'survey_type_id', 'gender_id'));
        
        $validAlpha = new Zend_Validate_Alpha(true);
        
        $validAlnum = new Zend_Validate_Alnum();
        $validAlnum->setMessage('Username must be letters or numbers', Zend_Validate_Alnum::NOT_ALNUM);
        
        $validEmail = new ValidateEmailAddress(); // see end of file for this class
        $validEmail->setMessage('Email address is not valid');
        
        $validPassword = new Zend_Validate_Regex('/.{5,}/');
        $validPassword->setMessage('Password must be at least 5 characters');
        
        $yearRange = new Zend_Validate_InArray(range(1930, 2000));
        $yearRange->setMessage('Birth year must be between 1930 and 2000 inclusive');

        $filters = array ();
        
        $validators = array(
            'first_name' => array(
                new Zend_Validate_NotEmpty()
                , Zend_Filter_Input::MESSAGES => 'First name is required'
                , $validAlpha
            )
            , 'last_name' => array(
                new Zend_Validate_NotEmpty()
                , Zend_Filter_Input::MESSAGES => 'Last name is required'
                , $validAlpha
            )
            , 'username' => array(
                new Zend_Validate_NotEmpty()
                , Zend_Filter_Input::MESSAGES => 'Username is required'
                , $validAlnum
                , new Api_ValidateUniqueUsername(Db_Pdo::getPdo()->prepare('SELECT * FROM user WHERE username = ?'))
            )
            , 'email' => array(
                new Zend_Validate_NotEmpty()
                , Zend_Filter_Input::MESSAGES => 'Email is required'
                , $validEmail
                , new Api_ValidateUniqueEmail(Db_Pdo::getPdo()->prepare('SELECT * FROM user_email WHERE email = ?'))
            )
            , 'password' => array(
                new Zend_Validate_NotEmpty()
                , Zend_Filter_Input::MESSAGES => 'Password is required'
                , $validPassword
            )
            , 'gender_id' => array(
                new Zend_Validate_NotEmpty()
                , Zend_Filter_Input::MESSAGES => 'Gender is required'
            )
            , 'birth_year' => array(
                new Zend_Validate_NotEmpty()
                , Zend_Filter_Input::MESSAGES => 'Birth year is required'
                , $yearRange
            )
            , 'email_frequency_id' => array(
                new Zend_Validate_NotEmpty()
                , Zend_Filter_Input::MESSAGES => 'Email frequency is required'
            )
            , 'poll_frequency_id' => array(
                new Zend_Validate_NotEmpty()
                , Zend_Filter_Input::MESSAGES => 'Poll frequency is required'
            )
            , 'survey_type_id' => array(
                new Zend_Validate_NotEmpty()
                , Zend_Filter_Input::MESSAGES => 'Select at least one survey type'
            )
            , 'ethnicity_id' => array(
                Zend_Filter_Input::ALLOW_EMPTY => true
            )
            , 'timezone' => array(
                Zend_Filter_Input::ALLOW_EMPTY => true
            )
        );
        $input = new Zend_Filter_Input($filters, $validators);
        $input->setData($this->_request->getPost());
        
        $passwordVerifyValidated = false;
                
        while ($input->isValid()) {
            // always validate password verify after everything else
            // is validated, so we don't have conflicting messages
            if (!$passwordVerifyValidated)
            {
                $email = new User_Email();
                $email->email = $input->email;
                
                // create user object with filtered data 
                // (before resetting the validator) 
                $user = new User(); 
                $user->setPlainTextPassword($input->password);
                $user->username =      $input->username;
                $user->first_name =    $input->first_name;
                $user->last_name =     $input->last_name;
                $user->gender_id =     $input->gender_id;
                $user->ethnicity_id =  $input->ethnicity_id;
                $user->birthdate =     $input->birth_year . '-00-00'; // ensure mysql date field accepts year
                $user->timezone =      urldecode($input->timezone);
            
                $preference = new Preference_General();
                $preference->poll_frequency_id = $input->poll_frequency_id;
                $preference->email_frequency_id = $input->email_frequency_id;
                $user->setPreference($preference);
                
                $surveyTypes = new Preference_SurveyTypeCollection();
                foreach ($input->survey_type_id as $surveyTypeId) {
                    $surveyTypes->addItem(new Preference_SurveyType(array('survey_type_id' => $surveyTypeId)));
                }
                $user->setSurveyTypes($surveyTypes);
                
                // now setup validation of password verify
                $validIdentical = new Zend_Validate_Identical($this->password);
                $validIdentical->setMessage('Passwords do not match', Zend_Validate_Identical::NOT_SAME);
                
                $input = new Zend_Filter_Input($filters, array(
                	'password_verify' => array(
                        new Zend_Validate_NotEmpty()
                        , Zend_Filter_Input::MESSAGES => 'Please repeat password'
                        , $validIdentical
                    )
                ));
                $input->setData($this->_request->getParams());
                $passwordVerifyValidated = true;
                // validate again
                continue; 
            }
            
            $user->setEmail($email);
            
            // save
            $user->save();
            
            // start up the user authentication session
            $this->_startUserSession($user);
            
            // success
            return $this->_resultType($user);
        }
        // validation error
        return $this->_resultType(new Api_ValidationError($input->getMessages()));
    }

    public function loginAction () {
        
        // end-user validation
        $validators = array(
            'username' => array(
                new Zend_Validate_NotEmpty(),
                Zend_Filter_Input::MESSAGES => 'Required'
            ),
            'password' => array(
                new Zend_Validate_NotEmpty(),
                Zend_Filter_Input::MESSAGES => 'Required'
            )
        );
        $validator = new Zend_Filter_Input(array(), $validators);
        $validator->setData($this->_request->getParams());
        
        if ($validator->isValid()) {
            
            $username = $validator->username;
            $plainTextPassword = $validator->password;
            
            // attempt getting the user
            $userRow = Db_Pdo::fetch('SELECT * FROM user WHERE username = ?', $username);
            
            // no user found
            if (empty($userRow)) {
                throw new Api_UserException(new Api_ValidationError(array('username' => array('Not found'))));
            }
            
            // calculate the password hash using the retreived password salt
            $passwordHash = md5(md5($plainTextPassword) . $userRow['password_salt']);
            
            // compare provided password to saved password
            if ($userRow['password'] !== $passwordHash) {
                throw new Api_UserException(new Api_ValidationError(array('password' => array('Password invalid'))));
            }
            
            // all is good, return the user
            $user = new User();
            $user->setData($userRow);
            
            // start up the user authentication session
            $this->_startUserSession($user);
            
            return $this->_resultType($user);
            
        } else {
            // form validation error
            throw new Api_UserException(new Api_ValidationError($validator->getMessages()));
        }
    }
    
    protected function _startUserSession (User & $user) {
        
        $userSession = Api_UserSession::getInstance();
        
        // set the user id on the session
        // which effectively resets the user and removes any
        // persistente user objects, thereby forcing rebuild
        $userSession->setId($user->getId());
        
        // re-query the user to pull in complete aggregate data
        $user = $userSession->getUser();
        
        // set the key on the user object so it is available for client-apps
        // this is important so they can authenticate correctly with the user_key
        $user->setKey($userSession->getKey());
    }    

}

class ValidateEmailAddress extends Zend_Validate_EmailAddress
{
    public function isValid($value)
    {
        $valid = parent::isValid($value);
        if (!$valid)
        {
            $this->_errors = array();
            $this->_messages = array();
            $this->_error(parent::INVALID);
        }
        return $valid;
    }
}
/*
Array
(
    [username] => dbj
    [password] => asdf
    [password_verify] => sdff
    [first_name] => David
    [last_name] => James
    [email] => david@davidbjames.info
    [gender] => male
    [ethnicity] => white
    [birth_year] => 1992
    [email_frequency] => 1
    [poll_frequency] => 2
    [survey_type] => Array
        (
            [0] => 2
            [1] => 3
        )

)
*/
