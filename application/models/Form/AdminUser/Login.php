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
			->setLabel('Email address:')
			->addValidator(new Zend_Validate_EmailAddress())
			->setRequired(true);
		
		$passwPassword =
			$this->createElement('password', 'passwPassword')
			->setLabel('Password:')
			->addValidator(new Zend_Validate_NotEmpty())
			->setRequired(true);

		$submitBtn =
			$this->createElement('submit', 'submitBtn')
				->setLabel('Submit');

		$this->addElements(
			array
			(
				$txtLogin,
				$passwPassword,
			)
		);

		/*$this->addDisplayGroup(
			array
				(
					$txtLogin,
					$passwPassword,
				),
			'group-login',
			array('Legend' => 'Login')
		);*/

		$this->addElements(
			array
			(
				$submitBtn,
			)
		);
	}
}