<?php
/**
 * Starbar actions in this controller are for local testing,
 * using an environment (via actions/views) that mimics the browser app.
 * Each view brings in the Remote equivalent via partial()
 * 
 * @see RemoteController for actual Starbars 
 * @author davidbjames
 */
class Starbar_IndexController extends Api_AbstractController
{
    public function indexAction () {
        
    }
    
    public function gagaAction()
    {
        $this->view->headLink()->appendStylesheet('/css/starbar-qualified.css');
        $this->view->headScript()->appendFile('/js/starbar/jquery-1.6.1.min.js');
    }
    
    public function genericAction () {
        $this->view->headLink()->appendStylesheet('/css/starbar-generic.css');
        $this->view->headScript()->appendFile('/js/starbar/jquery-1.6.1.min.js');
    }
   
    public function hellomusicAction () {
        $this->view->headLink()->appendStylesheet('/css/starbar-generic.css');
        $this->view->headLink()->appendStylesheet('/css/starbar-hellomusic.css');
        $this->view->headScript()->appendFile('/js/starbar/jquery-1.6.1.min.js');
    }
}

