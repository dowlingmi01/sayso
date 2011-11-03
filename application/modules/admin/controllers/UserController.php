<?php
/**
 * @author alecksmart
 */
class Admin_UserController extends Admin_CommonController
{

    public function init()
    {
        parent::init();
    }

    public function loginAction()
    {
        $this->view->form = new Form_AdminUser_Login();
    }
}
