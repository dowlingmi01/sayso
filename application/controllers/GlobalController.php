<?php

class GlobalController extends Api_AbstractController
{
    public function init() {
        if (!$this->_request->isXmlHttpRequest()) {
            $this->view->doctype('XHTML1_STRICT');
            $this->view->headLink()->appendStylesheet('/css/sayso-corporate.css', 'screen');
            $scripts = $this->view->headScript();
            $scripts->appendFile('/js/jquery-1.6.1.min.js');
            $scripts->appendFile('/js/jquery.form.min.js');
            $scripts->appendFile('/js/pubsub.js');
            $scripts->appendFile('/js/main.js');
        }
        parent::init();
    }
}