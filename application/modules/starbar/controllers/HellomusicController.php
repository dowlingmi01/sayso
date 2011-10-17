<?php

require_once APPLICATION_PATH . '/modules/starbar/controllers/ContentController.php';

class Starbar_HellomusicController extends Starbar_ContentController
{
    public function postDispatch() {
		if (!$this->_usingJsonPRenderer) {
        	$this->view->headLink()->appendStylesheet('/css/starbar-hellomusic.css');
        }

        parent::postDispatch();
	}

	// Daily deals is probably unique to each starbar
    public function dailyDealsAction ()
    {
		$feedUrl = "http://www.hellomusic.com/ec/Interpret.aspx?auth=uyskCsCO5jeS2d1fc5";

		$feed = null;
		$cache = Api_Registry::get('cache');
		$key = 'dailydeals';

		if ($cache->test($key)) {
			$feed = $cache->load($key);
		}

		// if the feed is no longer cached, or if it's empty for whatever reason, re-set the cache
		if (!$feed) {
			$handle = fopen($feedUrl, 'r');
			$feed = stream_get_contents($handle);

			// cache until the next update
			$cache->setLifetime(3600);

			$cache->save($feed);
		}

		$xml = simpleXML_load_string($feed, "SimpleXMLElement", LIBXML_NOCDATA);
		
		if($xml ===  FALSE) {

		} else {
			$this->view->assign('deals', $xml);
			
			// award the user
		    Game_Starbar::getInstance()->viewPromos();
			
			$facebookCallbackUrl = "http://".BASE_DOMAIN."/starbar/hellomusic/facebook-post-result?shared_type=promos&shared_id=THE_DEAL_ID&user_id=".$this->user_id."&user_key=".$this->user_key."&auth_key=".$this->auth_key;
			$facebookDescription = "Like Music? You can get the Beat Bar from Hello Music, give your opinion, earn points, get FREE gear, as well as exclusive access to deeply discounted music gear.";
			$this->_assignShareInfoToView(null, null, $facebookCallbackUrl, null, $facebookDescription);
		}
    }
    
    public function pollsAction ()
    {
    	parent::pollsAction();
    	$surveyUserMap = new Survey_UserMap();
    	$primarySurveyTaken = $surveyUserMap->checkIfUserHasCompletedSurvey($this->user_id, 1);
    	$this->view->primary_survey_taken = $primarySurveyTaken;
    	
    	if (!$primarySurveyTaken) {
    		$this->view->count_new_polls = 5 - ($this->view->count_complete_polls + $this->view->count_archive_polls);
    		if ($this->view->count_new_polls < 0) $this->view->count_new_polls = 0;
		}
	}

    public function surveysAction ()
    {
    	parent::surveysAction();
    	$surveyUserMap = new Survey_UserMap();
    	$primarySurveyTaken = $surveyUserMap->checkIfUserHasCompletedSurvey($this->user_id, 1);
    	$this->view->primary_survey_taken = $primarySurveyTaken;

    	if (!$primarySurveyTaken) {
    		$this->view->count_new_surveys = 4 - ($this->view->count_complete_surveys + $this->view->count_archive_surveys);
    		if ($this->view->count_new_surveys < 0) $this->view->count_new_surveys = 0;
		}
	}

    public function userProfileAction ()
    {
    	parent::userProfileAction();
    	$surveyUserMap = new Survey_UserMap();
    	$primarySurveyTaken = $surveyUserMap->checkIfUserHasCompletedSurvey($this->user_id, 1);
    	$this->view->primary_survey_taken = $primarySurveyTaken;

    	if (!$primarySurveyTaken) {
    		$this->view->count_new_polls = 5 - ($this->view->count_complete_polls + $this->view->count_archive_polls);
    		if ($this->view->count_new_polls < 0) $this->view->count_new_polls = 0;

    		$this->view->count_new_surveys = 4 - ($this->view->count_complete_surveys + $this->view->count_archive_surveys);
    		if ($this->view->count_new_surveys < 0) $this->view->count_new_surveys = 0;
		}
	}
}
