<?php
/**
 * @author alecksmart
 */

abstract class Admin_CommonController extends Zend_Controller_Action
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
     * @var AdminUser
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
    }

    /**
     * Set basic scripts for layout
     */
    protected function setLayoutBasics()
    {
        //$this->_helper->layout->setLayout('admin');
        $this->view->headLink()->appendStylesheet('/modules/common.css', 'screen');
        if(get_class($this) == 'Admin_IndexController')
        {
            // legacy styling
            $this->view->headLink()->appendStylesheet('/css/smoothness/jquery-ui-1.8.13.custom.css', 'screen');
        }
        else
        {
            $this->view->headLink()->appendStylesheet('/css/absolution/absolution.blue.css', 'screen');
        }
        $this->view->headScript()->appendFile('/js/jquery-1.6.1.min.js');
        $this->view->headScript()->appendFile('/js/jquery.form.min.js');
        $this->view->headScript()->appendFile('/modules/jquery-ui-1.8.16.full.min.js');
        $this->view->headScript()->appendFile('/modules/common.js');
    }

    /**
     * Checks if a role is connected to an Admin User
     *
     * @param array $roles - array of roles names
     */
    protected function checkAccess(array $roles = array())
    {
        if (!($this->currentUser instanceof AdminUser))
        {
            return false;
        }
        if (empty($roles))
        {
            return true;
        }
        $userRoles = $this->currentUser->getAdminRoles();
        //var_dump($userRoles->count());exit(0);
        foreach ($userRoles as $role)
        {
            foreach ($roles as $requiredRole)
            {
                if ($role->name == $requiredRole)
                {
                    return true;
                }
            }
        }
        return false;
    }

}