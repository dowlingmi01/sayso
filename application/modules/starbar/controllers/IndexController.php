<?php

class Starbar_IndexController extends Api_AbstractController
{

    public function init()
    {
        /* Initialize action controller here */
    }

    /**
     * This is the "static" Starbar used for testing/development only.
     * Since it is outside of the browser app context it has limited functionality
     * @uses self::remoteAction (see view partial)
     */
    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet('/css/starbar-qualified.css');
        $this->view->headScript()->appendFile('/js/starbar/jquery-1.6.1.min.js');
    }

    /**
     * This is the live Starbar accessed from the browser app
     * - it is returned via JSON-P 
     */
    public function remoteAction () 
    {
        $this->_enableRenderer(new Api_Plugin_JsonPRenderer());
        $this->render();
        return $this->_resultType(new Object(array('html' => $this->getResponse()->getBody())));
    }
    
    public function genericAction () {
        $this->view->headLink()->appendStylesheet('/css/starbar-generic.css');
        $this->view->headLink()->appendStylesheet('/css/jquery-ui-1.8.13.custom.css');
        $this->view->headScript()->appendFile('/js/starbar/jquery-1.6.1.min.js');
        $this->view->headScript()->appendFile('/js/starbar/jquery-ui-1.8.13.custom.min.js');
    }
    
    public function genericRemoteAction () {
        
    }
    
    public function hellomusicAction () {
				//$this->view->headLink()->appendStylesheet('/css/smoothness/jquery-ui-1.8.16.custom.css');
				$this->view->headLink()->appendStylesheet('/css/jquery.jscrollpane.css');
        $this->view->headLink()->appendStylesheet('/css/starbar-generic.css');
        $this->view->headLink()->appendStylesheet('/css/starbar-hellomusic.css');
        $this->view->headScript()->appendFile('/js/starbar/jquery-1.6.1.min.js');
        $this->view->headScript()->appendFile('/js/starbar/jquery-ui-1.8.16.custom.min.js');
        $this->view->headScript()->appendFile('/js/starbar/jquery.jscrollpane.min.js');
    }
    
    public function hellomusicRemoteAction () {
        
    }
}

