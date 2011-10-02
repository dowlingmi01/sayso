<?php

require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Api_IndexController extends Api_GlobalController
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        return $this->_resultType(new Object(array('foo' => 'bar')));
    }


}

