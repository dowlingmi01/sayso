<?php

class GlobalController extends Api_AbstractController
{
	public function init() {
		if (get_class($this->_request) != "Zend_Controller_Request_Simple" && !$this->_request->isXmlHttpRequest()) {
			$config = Api_Registry::getConfig();
			$this->view->doctype('XHTML1_STRICT');
			$this->view->headLink()->appendStylesheet('/css/sayso-corporate.css', 'screen');
			$scripts = $this->view->headScript();
			$scripts->appendFile('/js/jquery-1.7.1.min.js');
			$scripts->appendFile('/js/jquery.form.min.js');
			$scripts->appendFile('/js/pubsub.js');
			$scripts->appendFile('/js/jquery.lightbox_me.js');
			$scripts->appendFile('/js/bind.js');
			$scripts->appendFile('/js/main.js');
			$scripts->appendScript('a.api.authKey = "' . $config->api->authKey . '"; a.api.imageKey = "' . $config->api->imageKey . '";');
		}
		parent::init();
	}
}