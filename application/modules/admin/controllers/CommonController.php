<?php

/**
 * @author alecksmart
 */
class Admin_CommonController extends Zend_Controller_Action
{
    /**
     * @var Zend_Auth
     */
    protected $auth;

    /**
     * @var Zend_Controller_Action_Helper_Redirector
     */
    protected $rd;

    /**
     * @var Zend_Controller_Action_Helper_FlashMessenger
     */
    protected $msg;

    /**
     *
     * @var AdmiUser
     */
    protected $currentUser;
    
    /**
     *
     * @var User
     */
    protected $currentUser;

    /**
     * This function must be explicitly called for every controller action when auth is needed
     */
    public function init()
    {
        // create some useful shortcuts
        if (!$this->msg)
        {
            $this->msg = $this->_helper->FlashMessenger;
        }
        if (!$this->rd)
        {
            $this->rd = $this->_helper->Redirector;
        }
        if (!$this->auth)
        {
            $this->auth = Zend_Auth::getInstance();
        }

        // see if we have a logged in user
        $this->currentUser = null;
        if ($this->auth->hasIdentity())
		{
            $this->currentUser = AdminUser::getByEmail($this->auth->getIdentity());
        }
        else
        {
            $this->auth->clearIdentity();
        }

        // do other routines
        parent::init();
    }

    /**
     * Checks if a role is connected to an Admin User
     * 
     * @param AdminUser $user
     * @param array $roles - array of roles names 
     */
    protected function checkAccess(AdminUser $user, array $roles = array())
	{

	}

}