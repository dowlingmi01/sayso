<?php

require_once APPLICATION_PATH . '/modules/admin/controllers/CommonController.php';
/**
 * @author alecksmart
 */
class Admin_UserController extends Admin_CommonController
{
    public function init()
    {        
        //parent::init();
    }

    public function loginAction()
    {
        $this->_helper->layout->setLayout('empty');        
        //$this->view->form = new Form_AdminUser_Login();
    }
}
