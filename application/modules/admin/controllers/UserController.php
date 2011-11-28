<?php
/**
 * @author alecksmart
 */

require_once APPLICATION_PATH . '/modules/admin/controllers/CommonController.php';

class Admin_UserController extends Admin_CommonController
{
    public function init()
    {
        parent::init();

        if (!$this->_request->isXmlHttpRequest())
        {
            $this->setLayoutBasics();
        }
    }

    public function indexAction()
    {
        if(!$this->checkAccess(array('superuser')))
        {
            $this->_helper->viewRenderer->setNoRender(true);
        }

        $this->view->headScript()->appendFile('/modules/admin/user/index.js');        
        $this->view->addLink = '<a href="' . $this->view->url(array('action' => 'add')) . '">Add New</a>';
    }

    public function loginAction()
    {
        if(!$this->_request->isXmlHttpRequest() || $this->auth->hasIdentity())
        {
            die('You are not supposed to be here...');
        }

        $this->_helper->layout->setLayout('empty');
        $form = new Form_AdminUser_Login();
        // String used for javascript
        $this->view->loginResult = array('ok' => false, 'message' => array());

        if ($this->_request->isPost() && $form->isValid($_POST))
		{
            try
            {
                $emailAddress = $form->getValue('txtLogin');
                $password = $form->getValue('passwPassword');

                $adapter = new Zend_Auth_Adapter_DbTable(Zend_Registry::get('db'), 'admin_user', 'email', 'password', 'MD5(?)');
                $adapter->setIdentity($emailAddress)->setCredential($password);
                $result = $this->auth->authenticate($adapter);

                if($result->isValid())
                {
                    $this->view->loginResult['ok'] = true;
                }
                else
                {
                    $this->view->loginResult['message'][] = 'Login failed';
                }
            }
            catch(Exception $e)
            {
                $this->view->loginResult['message'][]   = 'Login failed with exception';
                if(getenv('APPLICATION_ENV') != 'production')
                {
                    $this->view->loginResult['message'][] = $e->getMessage();
                }
            }
        }

        $this->view->form = $form;
    }

    public function logoutAction()
    {
        if(!$this->_request->isXmlHttpRequest())
        {
            $this->rd->gotoSimple('index', 'index', 'admin');
        }
        $this->auth->clearIdentity();
    }

}
