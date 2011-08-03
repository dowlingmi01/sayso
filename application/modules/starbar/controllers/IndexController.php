<?php

class Starbar_IndexController extends Api_AbstractController
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
    }

    public function remoteAction () 
    {
        $this->_enableRenderer(new Api_Plugin_JsonPRenderer());
        $this->render();
        return $this->_resultType(new Object(array('html' => $this->getResponse()->getBody(), 'base_domain' => Api_Registry::getConfig()->baseDomain)));
    }
}

