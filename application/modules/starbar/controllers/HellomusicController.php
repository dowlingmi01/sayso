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
		$cache = Api_Cache::getInstance('Client_HelloMusic_dailyDeals');

		if ($cache->test()) {
			$feed = $cache->load();
		}

		// if the feed is no longer cached, or if it's empty for whatever reason, re-set the cache
		if (!$feed) {
			$handle = fopen($feedUrl, 'r');
			$feed = stream_get_contents($handle);
			$cache->save($feed);
		}

		$xml = simpleXML_load_string($feed, "SimpleXMLElement", LIBXML_NOCDATA);

		if($xml ===  FALSE) {

		} else {
			$this->view->assign('deals', $xml);

			// award the user
			Game_Starbar::getInstance()->viewPromos();

			$facebookCallbackUrl = "https://".BASE_DOMAIN."/starbar/hellomusic/facebook-post-result?shared_type=promos&shared_id=THE_DEAL_ID&user_id=".$this->user_id."&user_key=".$this->user_key."&auth_key=".$this->auth_key;
			$facebookDescription = "Like Music? You can get the Say.So Music Bar from Hello Music, give your opinion, earn points, get FREE gear, as well as exclusive access to deeply discounted music gear.";
			$this->_assignShareInfoToView(null, null, null, $facebookCallbackUrl, null, $facebookDescription);
		}
	}

	public function pollsAction ()
	{
		$surveyResponse = new Survey_Response();
		$primarySurveyTaken = $surveyResponse->checkIfUserHasCompletedSurvey($this->user_id, 1);
		$this->view->primary_survey_taken = $primarySurveyTaken;

		if (!$primarySurveyTaken) {
			$this->_maximumDisplayed['polls'] = 5;
		}
		parent::pollsAction();
	}

	public function surveysAction ()
	{
		$surveyResponse = new Survey_Response();
		$primarySurveyTaken = $surveyResponse->checkIfUserHasCompletedSurvey($this->user_id, 1);
		$this->view->primary_survey_taken = $primarySurveyTaken;

		if (!$primarySurveyTaken) {
			$this->_maximumDisplayed['surveys'] = 4;
		}
		parent::surveysAction();
	}

	public function userProfileAction ()
	{
		$surveyResponse = new Survey_Response();
		$primarySurveyTaken = $surveyResponse->checkIfUserHasCompletedSurvey($this->user_id, 1);
		$this->view->primary_survey_taken = $primarySurveyTaken;

		if (!$primarySurveyTaken) {
			$this->_maximumDisplayed['polls'] = 5;
			$this->_maximumDisplayed['surveys'] = 4;
		}
		parent::userProfileAction();
	}

	protected function _assignShareInfoToView($shareLink = null, $twitterShareText = null, $facebookShareCaption = null, $facebookCallbackUrl = null, $facebookTitle = null, $facebookDescription = null) {
		parent::_assignShareInfoToView($shareLink, $twitterShareText, $facebookShareCaption, $facebookCallbackUrl, $facebookTitle, $facebookDescription);
		$this->view->assign('facebook_share_image_url', 'https://s3.amazonaws.com/say.so/media/hellomusic/logo_hellomusic.png');
	}
}
