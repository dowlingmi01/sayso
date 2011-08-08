<?php

class Admin_DataController extends Api_AbstractController
{

    public function init()
    {
//        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
//        $this->_disableLayout();
    }

    public function indexAction()
    {
        header('Content-type: application/json');
        exit(file_get_contents(APPLICATION_PATH . '/data/data.json'));
    }

    public function submitAction () {
        $this->_validateRequiredParameters(array('data'));
        file_put_contents(APPLICATION_PATH . '/data/data.json', $this->data);
        exit('{ status : "success" }');
    }

}

