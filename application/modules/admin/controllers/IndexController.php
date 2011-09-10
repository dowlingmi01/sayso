<?php

class Admin_IndexController extends Api_AbstractController
{

    public function init()
    {
        if (!$this->_request->isXmlHttpRequest()) {
            $config = Api_Registry::getConfig();
            $this->view->doctype('XHTML1_STRICT');
            $this->view->headLink()->appendStylesheet('/css/admin.css', 'screen');
            $this->view->headLink()->appendStylesheet('/css/smoothness/jquery-ui-1.8.13.custom.css', 'screen');
            $scripts = $this->view->headScript();
            $scripts->appendFile('/js/jquery-1.6.1.min.js');
            $scripts->appendFile('/js/jquery.form.min.js');
            $scripts->appendFile('/js/jquery-ui-1.8.13.custom.min.js');
            $scripts->appendFile('/js/pubsub.js');
            $scripts->appendFile('/js/jquery.lightbox_me.js');
            $scripts->appendFile('/js/mustache.js');
            $scripts->appendFile('/js/templates.js');
            $scripts->appendFile('/js/bind.js');
            $scripts->appendFile('/js/main.js');
            $scripts->appendFile('/js/admin.js');
            $scripts->appendScript('a.api.authKey = "' . $config->api->authKey . '"; a.api.imageKey = "' . $config->api->imageKey . '";');
        }
        parent::init();
    }

    public function indexAction()
    {
            
    }
    public function erasemeAction () 
    {
        
    }
}

