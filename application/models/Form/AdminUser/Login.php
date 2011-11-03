<?php
/**
 * Administrative user login form handling
 *
 * @author alecksmart
 */
class Form_AdminUser_Login extends Zend_Form
{

    public function init()
    {
        $this->setAttrib('id', 'user-login')
            ->setAttrib('onsubmit', 'return false;');

        $txtLogin =
            $this->createElement('text', 'txtLogin')
            ->setLabel('Login:')
            ->addValidator()
            ->setRequired(true);

        $this->addElements(
            array
            (
                $txtLogin
            )
        );

        $this->addDisplayGroup(
            array
                (
                    $txtLogin
                ),
            'group-login',
            array('Legend' => 'Login')
        );
    }
    
}