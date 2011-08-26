<?php

require_once APPLICATION_PATH . '/controllers/GlobalController.php';

class IndexController extends GlobalController
{

    public function indexAction()
    {
        $this->_redirect('/user/register');
    }
}
