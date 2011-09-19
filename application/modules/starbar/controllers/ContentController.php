<?php

class Starbar_ContentController extends Api_AbstractController
{
	// Render with JsonP by default
	protected $_usingJsonPRenderer = true;

    public function preDispatch() {
        if (!in_array($this->_request->getActionName(), array('index', 'gaga'))) {
            // i.e. for everything based on Generic Starbar, use these includes
            $this->view->headScript()->appendFile('/js/starbar/jquery-1.6.1.min.js');
            $this->view->headScript()->appendFile('/js/starbar/jquery-ui-1.8.16.custom.min.js');
            $this->view->headScript()->appendFile('/js/starbar/jquery.jscrollpane.min.js');
            $this->view->headScript()->appendFile('/js/starbar/jquery.cookie.js');
            $this->view->headScript()->appendFile('/js/starbar/jquery.jeip.js');
        }
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

    // Embed a single poll. Expects "survey_id" passed via URL (GET)
    public function hellomusicEmbedPollAction ()
    {
    	$this->view->headScript()->appendFile('/js/starbar/jquery-1.6.1.min.js');
        $this->view->headLink()->appendStylesheet('/css/starbar-generic.css');
        $this->view->headLink()->appendStylesheet('/css/starbar-hellomusic.css');
    	$this->_usingJsonPRenderer = false;
		$request = $this->getRequest();

		$survey_id = (int) $request->getParam('survey_id');

		if ($survey_id) {
			$survey = new Survey();
			$survey->loadData($survey_id);

			//switch ($survey->origin)
			$this->view->assign('poll_id', $survey->external_id);
			$this->view->assign('poll_key', $survey->external_key);
		}
    }

    // Fetches polls for the current user for display
    public function hellomusicPollsAction ()
    {
		$newSurveys = new SurveyCollection();
		$completeSurveys = new SurveyCollection();
		$archiveSurveys = new SurveyCollection();

		$newSurveys->loadSurveysForUser(1, 'poll', 'new');
		$completeSurveys->loadSurveysForUser(1, 'poll', 'complete');
		$archiveSurveys->loadSurveysForUser(1, 'poll', 'archive');

		$this->view->assign('new_surveys', $newSurveys);
		$this->view->assign('complete_surveys', $completeSurveys);
		$this->view->assign('archive_surveys', $archiveSurveys);
	}

    // Fetches surveys for the current user for display
    public function hellomusicSurveysAction ()
    {
		$newSurveys = new SurveyCollection();
		$completeSurveys = new SurveyCollection();
		$archiveSurveys = new SurveyCollection();

		$newSurveys->loadSurveysForUser(1, 'survey', 'new');
		$completeSurveys->loadSurveysForUser(1, 'survey', 'complete');
		$archiveSurveys->loadSurveysForUser(1, 'survey', 'archive');

		$this->view->assign('new_surveys', $newSurveys);
		$this->view->assign('complete_surveys', $completeSurveys);
		$this->view->assign('archive_surveys', $archiveSurveys);
    }

    public function embedSurveyAction ()
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
