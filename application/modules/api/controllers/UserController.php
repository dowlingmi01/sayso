<?php

class Api_UserController extends Api_AbstractController
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

