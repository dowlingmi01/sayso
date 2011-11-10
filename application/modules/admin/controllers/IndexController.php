<?php

require_once APPLICATION_PATH . '/modules/admin/controllers/CommonController.php';

class Admin_IndexController extends Admin_CommonController
{
    public function init()
    {

        parent::init();

        if (!$this->_request->isXmlHttpRequest())
        {
            $this->setLayoutBasics();
            $config = Api_Registry::getConfig();

            $scripts = $this->view->headScript();

            $scripts->appendFile('/js/pubsub.js');
            $scripts->appendFile('/js/jquery.lightbox_me.js');
            $scripts->appendFile('/js/mustache.js');
            $scripts->appendFile('/js/templates.js');
            $scripts->appendFile('/js/bind.js');
            //$scripts->appendFile('/js/main.js');
            $scripts->appendFile('/js/admin.js');
            //$scripts->appendScript('a.api.authKey = "' . $config->api->authKey . '"; a.api.imageKey = "' . $config->api->imageKey . '";' . PHP_EOL);
            // new login
            // @todo - get rid of this layout and use admin.phtml
            //$this->view->headLink()->appendStylesheet('/modules/common.css', 'screen');
            //$this->view->headScript()->appendFile('/modules/common.js');
            $this->view->headLink()->appendStylesheet('/modules/admin/index/index.css', 'screen');
        }
        /*if (!$this->_request->isXmlHttpRequest()) {
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
            // new login
            // @todo - get rid of this layout and use admin.phtml
            //$this->view->headLink()->appendStylesheet('/modules/common.css', 'screen');
            $this->view->headScript()->appendFile('/modules/common.js');
        }*/
    }

    public function indexAction()
    {

    }
    public function erasemeAction ()
    {

    }
}

