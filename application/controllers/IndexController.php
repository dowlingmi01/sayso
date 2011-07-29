<?php

class IndexController extends Api_AbstractController
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }

    public function registerSubmitAction () 
    {
        $this->_enableRenderer(new Api_Plugin_JsonRenderer());
        $this->_createMissingParameters(array('email_frequency', 'poll_frequency', 'survey_type', 'gender'));
        
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
            , 'gender' => array(
                new Zend_Validate_NotEmpty()
                , Zend_Filter_Input::MESSAGES => 'Gender is required'
            )
            , 'birth_year' => array(
                new Zend_Validate_NotEmpty()
                , Zend_Filter_Input::MESSAGES => 'Birth year is required'
                , $yearRange
            )
            , 'email_frequency' => array(
                new Zend_Validate_NotEmpty()
                , Zend_Filter_Input::MESSAGES => 'Email frequency is required'
            )
            , 'poll_frequency' => array(
                new Zend_Validate_NotEmpty()
                , Zend_Filter_Input::MESSAGES => 'Poll frequency is required'
            )
            , 'survey_type' => array(
                new Zend_Validate_NotEmpty()
                , Zend_Filter_Input::MESSAGES => 'Select at least one survey type'
            )
            , 'ethnicity' => array()
            , 'timezone' => array()
        );
        $input = new Zend_Filter_Input($filters, $validators);
        $input->setData($this->_request->getPost());
        
        $passwordVerifyValidated = false;
                
        while ($input->isValid()) {
            // always validate password verify after everything else
            // is validated, so we don't have conflicting messages
            if (!$passwordVerifyValidated)
            {
                $email = new Email();
                $email->email = $input->email;
                
                // create user object with filtered data 
                // (before resetting the validator) 
                $user = new User(); 
                $user->setPlainTextPassword($input->password);
                $user->username =      $input->username;
                $user->first_name =    $input->first_name;
                $user->last_name =     $input->last_name;
                $user->gender =        $input->gender;
                $user->ethnicity =     $input->ethnicity;
                $user->birthdate =     $input->birth_year . '-00-00'; // ensure mysql date field accepts year
                $user->timezone =      urldecode($input->timezone);
            
                $preference = new PreferenceGeneral();
                $preference->poll_frequency_id = $input->poll_frequency;
                $preference->email_frequency_id = $input->email_frequency;
                $user->setPreference($preference);
                
                $surveyTypes = new PreferenceSurveyTypeCollection();
                foreach ($input->survey_type as $surveyTypeId) {
                    $surveyTypes->addItem(new PreferenceSurveyType(array('survey_type_id' => $surveyTypeId)));
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
            
            // reload from db to ensure we send any default table data back in the response
            $user->reload();
            
            // success
            return $this->_resultType($user);
        }
        // validation error
        return $this->_resultType(new Api_ValidationError($input->getMessages()));
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