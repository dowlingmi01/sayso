<?php

require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Starbar_ContentController extends Api_GlobalController
{
	// Render with JsonP by default
	protected $_usingJsonPRenderer = true;

    public function postDispatch()
    {
    	if ($this->_usingJsonPRenderer) {
	        $this->_enableRenderer(new Api_Plugin_JsonPRenderer());
	        $this->render();
	        return $this->_resultType(new Object(array('html' => $this->getResponse()->getBody())));
		} else {
            $this->view->headScript()->appendFile('/js/starbar/jquery-1.6.1.min.js');
            $this->view->headScript()->appendFile('/js/starbar/jquery-ui-1.8.16.custom.min.js');
            $this->view->headScript()->appendFile('/js/starbar/jquery.jscrollpane.min.js');
            $this->view->headScript()->appendFile('/js/starbar/jquery.cookie.js');
            $this->view->headScript()->appendFile('/js/starbar/jquery.jeip.js');
        	$this->view->headLink()->appendStylesheet('/css/starbar-generic.css');
		}
    }

    public function aboutSaysoAction ()
    {

    }

    // Embed a single SG poll. Expects "survey_id" passed via URL (GET)
    public function embedPollAction ()
    {
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

    // Embed a single SG survey. Expects "survey_id" passed via URL (GET)
    public function embedSurveyAction ()
    {
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
    public function pollsAction ()
    {
		$newSurveys = new SurveyCollection();
		$completeSurveys = new SurveyCollection();
		$archiveSurveys = new SurveyCollection();

		$newSurveys->loadSurveysForStarBarAndUser(1, 1, 'poll', 'new');
		$completeSurveys->loadSurveysForStarBarAndUser(1, 1, 'poll', 'complete');
		$archiveSurveys->loadSurveysForStarBarAndUser(1, 1, 'poll', 'archive');

		$this->view->assign('new_surveys', $newSurveys);
		$this->view->assign('complete_surveys', $completeSurveys);
		$this->view->assign('archive_surveys', $archiveSurveys);
	}

    // Fetches surveys for the current user for display
    public function surveysAction ()
    {
		$newSurveys = new SurveyCollection();
		$completeSurveys = new SurveyCollection();
		$archiveSurveys = new SurveyCollection();

		$newSurveys->loadSurveysForStarBarAndUser(1, 1, 'survey', 'new');
		$completeSurveys->loadSurveysForStarBarAndUser(1, 1, 'survey', 'complete');
		$archiveSurveys->loadSurveysForStarBarAndUser(1, 1, 'survey', 'archive');

		$this->view->assign('new_surveys', $newSurveys);
		$this->view->assign('complete_surveys', $completeSurveys);
		$this->view->assign('archive_surveys', $archiveSurveys);
    }

    public function onboardingAction ()
    {

	}

    public function promosAction ()
    {

    }

    public function userProfileAction ()
    {

    }

    public function userLevelAction ()
    {

    }

    public function userPointsAction ()
    {

    }
}
