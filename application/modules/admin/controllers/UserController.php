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
    }

    public function loginAction()
    {
        $this->_helper->layout->setLayout('empty');
        $form = new Form_AdminUser_Login();
        $this->view->form = $form;
    }
}
