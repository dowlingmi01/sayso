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
    	// this page is fetched via an iframe, not ajax;
    	$this->_usingJsonPRenderer = false;

		$request = $this->getRequest();

		$surveyId = (int) $request->getParam('survey_id');

		if ($surveyId) {
			$survey = new Survey();
			$survey->loadData($surveyId);

			$this->view->assign('survey', $survey);

			$bundleOfJoy = $this->_getBundleOfJoy($surveyId);
			$this->view->assign('bundle_of_joy', $bundleOfJoy);
		}
    }

    // Embed a single SG survey. Expects "survey_id" passed via URL (GET)
    public function embedSurveyAction ()
    {
		$request = $this->getRequest();

		$surveyId = (int) $request->getParam('survey_id');

		if ($surveyId) {
			$survey = new Survey();
			$survey->loadData($surveyId);

			$this->view->assign('survey', $survey);

			$bundleOfJoy = $this->_getBundleOfJoy($surveyId);
			$this->view->assign('bundle_of_joy', $bundleOfJoy);
		}
	}

    public function surveyCompleteAction ()
    {
    	// this page is fetched via an iframe, not ajax;
    	$this->_usingJsonPRenderer = false;

		$request = $this->getRequest();
		$surveyId = $request->getParam('survey_id');
		if ($surveyId) {
			$survey = new Survey();
			$survey->loadData($surveyId);

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
    
    private function _getBundleOfJoy ($surveyId)
    {
    	$bundleOfJoy = "";
    	$sep = "^|^"; // seperator between variables
    	$eq = "^-^"; // seperator between variable name and value
    	// e.g. user_id^-^1^|^user_key^-^123
    	
    	$bundleOfJoy .= "user_id" . $eq . $this->user_id;
    	$bundleOfJoy .= $sep;
    	$bundleOfJoy .= "user_key" . $eq . $this->user_key;
    	$bundleOfJoy .= $sep;
    	$bundleOfJoy .= "auth_key" . $eq . $this->auth_key;
    	$bundleOfJoy .= $sep;
    	$bundleOfJoy .= "survey_id" . $eq . $this->survey_id;
    	
    	return $bundleOfJoy;
	}
}
