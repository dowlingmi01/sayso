<?php

class Starbar_ContentController extends Api_AbstractController
{
	protected $_usingJsonPRenderer = true;

    public function init()
    {
        /* Initialize action controller here */
    }

    public function postDispatch()
    {
    	if ($this->_usingJsonPRenderer) {
	        $this->_enableRenderer(new Api_Plugin_JsonPRenderer());
	        $this->render();
	        return $this->_resultType(new Object(array('html' => $this->getResponse()->getBody())));
		}
    }

    public function aboutSaysoAction ()
    {

    }

    public function embedPollAction ()
    {
    	$this->view->headScript()->appendFile('/js/starbar/jquery-1.6.1.min.js');
    	$this->_usingJsonPRenderer = false;
		$request = $this->getRequest();
		$this->view->assign('poll_id', $request->getParam('poll_id'));
		$this->view->assign('poll_key', $request->getParam('poll_key'));
    }

    public function hellomusicPollsAction ()
    {

    }

    public function hellomusicSurveysAction ()
    {

    }

    public function hellomusicDailyDealsAction ()
    {

    }

    public function hellomusicPromosAction ()
    {

    }

    public function hellomusicUserProfileAction ()
    {

    }

    public function hellomusicUserLevelAction ()
    {

    }

    public function hellomusicUserPointsAction ()
    {

    }

}
