<?php

class Starbar_ContentController extends Api_AbstractController
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function postDispatch()
    {
        $this->_enableRenderer(new Api_Plugin_JsonPRenderer());
        $this->render();
        return $this->_resultType(new Object(array('html' => $this->getResponse()->getBody())));
    }

    public function aboutSaysoAction ()
    {

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
