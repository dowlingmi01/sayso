<?php

require_once APPLICATION_PATH . '/modules/starbar/controllers/ContentController.php';

class Starbar_HellomusicController extends Starbar_ContentController
{
	public function postDispatch() {
		parent::postDispatch();
		if (!$this->_usingJsonPRenderer) {
			$this->view->headLink()->appendStylesheet('/css/starbar-hellomusic.css');
		}
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

			$facebookCallbackUrl = "https://".BASE_DOMAIN."/starbar/hellomusic/facebook-post-result?shared_type=promos&shared_id=THE_DEAL_ID&user_id=".$this->user_id."&user_key=".$this->user_key."&starbar_id=".$this->starbar_id;
			$facebookDescription = "Like Music? You can get the Say.So Music Bar from Hello Music, give your opinion, earn points, get FREE gear, as well as exclusive access to deeply discounted music gear.";
			$this->_assignShareInfoToView(null, null, null, $facebookCallbackUrl, null, $facebookDescription);
		}
	}

	public function pollsAction ()
	{
		$this->view->primary_survey_taken = Survey_Response::checkIfUserHasCompletedSurvey($this->user_id, 1);

		if (!$primarySurveyTaken) {
			$this->_maximumDisplayed['polls'] = 5;
		}
		parent::pollsAction();
	}

	public function surveysAction ()
	{
		$this->view->primary_survey_taken = Survey_Response::checkIfUserHasCompletedSurvey($this->user_id, 1);

		if (!$primarySurveyTaken) {
			$this->_maximumDisplayed['surveys'] = 4;
		}
		parent::surveysAction();
	}

	public function userProfileAction ()
	{
		$this->view->primary_survey_taken = Survey_Response::checkIfUserHasCompletedSurvey($this->user_id, 1);

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
	protected $_appShareLink = 'http://music.say.so/';
	protected $_fbkAppDescription = "Like Music? You can get the Say.So Music Bar from Hello Music, give your opinion, earn points, get FREE gear, as well as exclusive access to deeply discounted music gear.";
	
	protected function _assignShareAppToView($facebookCallbackUrl) {
		$twAppShareText = 'Join me in the Say.So Music Bar app. Get access to sweet gear deals and a chance to win a Takamine Guitar';
		$fbkAppShareTitle = 'Say.So Music Bar';
		$fbkAppShareCopy = "If you're a Musician or dig music gear, you should join me in the Say.So Music Bar from Hello Music. We get access to some sweet gear deals and get awesome odds on walking away with one of their big giveaways like a Takamine Acoustic, a Full Midi Kit, and others. We just give our opinion on a few things and they give us Notes we can redeem for stuff. Sweet deal. Only lasts a month. Want in?";

		$this->_assignShareInfoToView($this->_appShareLink, $twAppShareText, $fbkAppShareCopy,  $facebookCallbackUrl, $fbkAppShareTitle, null);
	}
	protected function _assignShareSurveyToView(Survey $survey, $facebookCallbackUrl) {
		$twShareText = "I just answered " . $survey->title ." and earned 25 Snakkle Bucks! Join Snakkle Say.So and earn great prizes!";
		$fbkShareText = "Survey time! Just filled out '" . $survey->title ."' on the Say.So Music Bar";

		$this->_assignShareInfoToView($this->_appShareLink, $twShareText, $fbkShareText, $facebookCallbackUrl, $survey->title, $this->_fbkAppDescription);
	}
	protected function _assignSharePollToView(Survey $survey, $facebookCallbackUrl) {
		$twShareText = "I just answered " . $survey->title ." and earned 25 Snakkle Bucks! Join Snakkle Say.So and earn great prizes!";
		$fbkShareText = "Poll time! Just took the '" . $survey->title ."' poll on the Say.So Music Bar";

		$this->_assignShareInfoToView($this->_appShareLink, $twShareText, $fbkShareText, $facebookCallbackUrl, $survey->title, $this->_fbkAppDescription);
	}
	protected function _assignShareQuizToView(Survey $survey, $facebookCallbackUrl) {
		$twShareText = "I just answered " . $survey->title ." and earned 25 Snakkle Bucks! Join Snakkle Say.So and earn great prizes!";
		$fbkShareText = 'I just earned 25 Snakkle Bucks for sharing the quiz "'. $survey->title .'".
Join Snakkle Say.So and get access big giveaways and awesome prizes.';

		$this->_assignShareInfoToView($this->_appShareLink, $twShareText, $fbkShareText, $facebookCallbackUrl, $survey->title, $this->_fbkAppDescription);
	}
}
