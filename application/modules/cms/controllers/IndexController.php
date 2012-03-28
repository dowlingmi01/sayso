<?php

require_once APPLICATION_PATH . '/modules/admin/controllers/CommonController.php';

class Cms_IndexController extends Admin_CommonController
{
	public function init()
	{
		parent::init();

		if (!$this->_request->isXmlHttpRequest())
		{
			$this->setLayoutBasics();

			$scripts = $this->view->headScript();
			$scripts->appendFile('/js/pubsub.js');
			$scripts->appendFile('/js/jquery.lightbox_me.js');
			$scripts->appendFile('/js/mustache.js');
			$scripts->appendFile('/js/templates.js');
			$scripts->appendFile('/js/bind.js');
			$scripts->appendFile('/modules/admin/index/index.js');
			$this->view->headLink()->appendStylesheet('/modules/admin/index/index.css', 'screen');
		}
	}

	public function indexAction()
	{
		if(!$this->checkAccess(array('superuser')))
		{
			$this->_helper->viewRenderer->setNoRender(true);
		}
	}

	public function erasemeAction ()
	{
		if(!$this->checkAccess(array('superuser')))
		{
			die('Access denied!');
		}
	}
}

