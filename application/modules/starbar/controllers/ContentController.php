<?php

class Starbar_ContentController extends Api_AbstractController
{
	// Render with JsonP by default
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

    // Embed a single poll. Expects "survey_id" passed via URL (GET)
    public function embedPollAction ()
    {
    	$this->view->headScript()->appendFile('/js/starbar/jquery-1.6.1.min.js');
    	$this->_usingJsonPRenderer = false;
		$request = $this->getRequest();

		$surveyId = (int) $request->getParam('survey_id');

		if ($surveyId) {
			$survey = new Survey();
			$survey->loadData($surveyId);

			//switch ($survey->origin)
			$this->view->assign('poll_id', $survey->external_id);
			$this->view->assign('poll_key', $survey->external_key);
		}
    }

    // Fetches polls for the current user for display
    public function hellomusicPollsAction ()
    {
		$surveys = new SurveyCollection();
		$surveys->getSurveysForUser(1, 'poll', 'new');
		$new_surveys = clone $surveys;
		$surveys->getSurveysForUser(1, 'poll', 'complete');
		$complete_surveys = clone $surveys;
		$surveys->getSurveysForUser(1, 'poll', 'archive');
		$archive_surveys = clone $surveys;

		$this->view->assign('new_surveys', $new_surveys);
		$this->view->assign('complete_surveys', $complete_surveys);
		$this->view->assign('archive_surveys', $archive_surveys);
	}

    // Fetches surveys for the current user for display
    public function hellomusicSurveysAction ()
    {
		$surveys = new SurveyCollection();
		$surveys->getSurveysForUser(1, 'survey', 'new');
		$new_surveys = clone $surveys;
		$surveys->getSurveysForUser(1, 'survey', 'complete');
		$complete_surveys = clone $surveys;
		$surveys->getSurveysForUser(1, 'survey', 'archive');
		$archive_surveys = clone $surveys;

		$this->view->assign('new_surveys', $new_surveys);
		$this->view->assign('complete_surveys', $complete_surveys);
		$this->view->assign('archive_surveys', $archive_surveys);
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
