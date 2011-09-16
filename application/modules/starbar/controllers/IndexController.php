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
    public function preDispatch() {
        if (!in_array($this->_request->getActionName(), array('index', 'gaga'))) {
            // i.e. for everything based on Generic Starbar, use these includes
            $this->view->headLink()->appendStylesheet('/css/starbar-generic.css');
            $this->view->headScript()->appendFile('/js/starbar/jquery-1.6.1.min.js');
            $this->view->headScript()->appendFile('/js/starbar/jquery-ui-1.8.16.custom.min.js');
            $this->view->headScript()->appendFile('/js/starbar/jquery.jscrollpane.min.js');
            $this->view->headScript()->appendFile('/js/starbar/jquery.cookie.js');
            $this->view->headScript()->appendFile('/js/starbar/jquery.jeip.js');
        }
    }
    
    public function indexAction () {
        
    }
    
    public function gagaAction()
    {
        $this->view->headLink()->appendStylesheet('/css/starbar-qualified.css');
        $this->view->headScript()->appendFile('/js/starbar/jquery-1.6.1.min.js');
    }
    
    public function genericAction () {
    }
   
    public function hellomusicAction () {
        $this->view->headLink()->appendStylesheet('/css/starbar-hellomusic.css');
        
    }
}

