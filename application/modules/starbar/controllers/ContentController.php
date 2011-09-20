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

			$this->view->assign('survey', $survey);
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

			$this->view->assign('survey', $survey);
		}
    }

    // Fetches polls for the current user for display
    public function pollsAction ()
    {
		$newSurveys = new SurveyCollection();
		$completeSurveys = new SurveyCollection();
		$archiveSurveys = new SurveyCollection();

		$newSurveys->loadSurveysForStarbarAndUser(1, 1, 'poll', 'new');
		$completeSurveys->loadSurveysForStarbarAndUser(1, 1, 'poll', 'complete');
		$archiveSurveys->loadSurveysForStarbarAndUser(1, 1, 'poll', 'archive');

		$this->view->assign('new_surveys', $newSurveys);
		$this->view->assign('complete_surveys', $completeSurveys);
		$this->view->assign('archive_surveys', $archiveSurveys);

		$this->view->assign('count_new_surveys', sizeof($newSurveys));
		$this->view->assign('count_complete_surveys', sizeof($completeSurveys));
		$this->view->assign('count_archive_surveys', sizeof($archiveSurveys));
	}

    // Fetches surveys for the current user for display
    public function surveysAction ()
    {
		$newSurveys = new SurveyCollection();
		$completeSurveys = new SurveyCollection();
		$archiveSurveys = new SurveyCollection();

		$newSurveys->loadSurveysForStarbarAndUser(1, 1, 'survey', 'new');
		$completeSurveys->loadSurveysForStarbarAndUser(1, 1, 'survey', 'complete');
		$archiveSurveys->loadSurveysForStarbarAndUser(1, 1, 'survey', 'archive');

		$this->view->assign('new_surveys', $newSurveys);
		$this->view->assign('complete_surveys', $completeSurveys);
		$this->view->assign('archive_surveys', $archiveSurveys);

		$this->view->assign('count_new_surveys', sizeof($newSurveys));
		$this->view->assign('count_complete_surveys', sizeof($completeSurveys));
		$this->view->assign('count_archive_surveys', sizeof($archiveSurveys));
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
