<?php
/**
 * Administrative user login form handling
 *
 * @author alecksmart
 */
class Form_AdminUser_AddEdit extends Zend_Form
{

    /**
     * @var AdminUser
     */
    private $user;

    public function setUser(AdminUser $user)
    {
        $this->user = $user;
    }

    public function buildDeferred()
    {
        $this->setAttrib('id', 'formEntity');

        $txtLogin =
            $this->createElement('text', 'txtLogin')
            ->setLabel('Email address:')
            ->addValidator(new Zend_Validate_EmailAddress())
            ->setRequired(true);
        
        $passwPassword =
            $this->createElement('password', 'passwPassword')
            ->setLabel('Password:');

        // Password needed for new users
        if(!($this->user instanceof AdminUser))
        {
            $passwPassword
                ->addValidator(new Zend_Validate_NotEmpty())
                ->setRequired(true);
        }

        $txtFirstName =
            $this->createElement('text', 'txtFirstName')
            ->setLabel('First Name:')
            ->addValidator(new Zend_Validate_NotEmpty())
            ->setRequired(true);

        $txtLastName =
            $this->createElement('text', 'txtLastName')
            ->setLabel('Last Name:')
            ->addValidator(new Zend_Validate_NotEmpty())
            ->setRequired(true);

        $submitBtn =
            $this->createElement('submit', 'submitBtn')
                ->setLabel('Submit');

        $this->addElements(
            array
            (
                $txtFirstName,
                $txtLastName,
                $txtLogin,
                $passwPassword,
            )
        );


        $this->addElements(
            array
            (
                $submitBtn,
            )
        );
    }
}