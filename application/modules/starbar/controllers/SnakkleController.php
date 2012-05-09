<?php

require_once APPLICATION_PATH . '/modules/starbar/controllers/ContentController.php';

class Starbar_SnakkleController extends Starbar_ContentController
{
	public function postDispatch() {
		parent::postDispatch();
		if (!$this->_usingJsonPRenderer) {
			$this->view->headLink()->appendStylesheet('/css/starbar-snakkle.css');
		}
	}

	protected function _assignShareInfoToView($shareLink = null, $twitterShareText = null, $facebookShareCaption = null, $facebookCallbackUrl = null, $facebookTitle = null, $facebookDescription = null) {
		parent::_assignShareInfoToView($shareLink, $twitterShareText, $facebookShareCaption, $facebookCallbackUrl, $facebookTitle, $facebookDescription);
		$this->view->assign('facebook_share_image_url', 'https://s3.amazonaws.com/say.so/media/snakkle/logo_snakkle.png');
	}
	protected $_appShareLink = 'http://snakkle.say.so';
	protected $_fbkAppDescription = "Say.So is your way of making a lasting impact on the communities you love. Participating in Snakkle Say.So is easy - by giving your opinion, answering polls and taking fun quizzes, you gain points to redeem awesome prizes from Snakkle.";
	
	protected function _assignShareAppToView($facebookCallbackUrl) {
		$twAppShareText = 'Join Snakkle Say.So and get access to big giveaways and great prizes from Snakkle.';
		$fbkAppShareTitle = 'Snakkle Say.So';
		$fbkAppShareCopy = "I just earned 19 Snakkle Bucks for sharing Snakkle Say.So!
Join Snakkle Say.So and get access to big giveaways and awesome prizes.";

		$this->_assignShareInfoToView($this->_appShareLink, $twAppShareText, $fbkAppShareCopy,  $facebookCallbackUrl, $fbkAppShareTitle, $_fbkAppDescription);
	}
	protected function _assignShareSurveyToView(Survey $survey, $facebookCallbackUrl) {
		$twShareText = "I just answered " . $survey->title ." and earned 38 Snakkle Bucks! Join Snakkle Say.So and earn great prizes!";
		$fbkShareText = 'I just earned 38 Snakkle Bucks for answering the survey "'. $survey->title .'".
Join Snakkle Say.So and get access big giveaways and awesome prizes.';

		$this->_assignShareInfoToView($this->_appShareLink, $twShareText, $fbkShareText, $facebookCallbackUrl, $survey->title, $this->_fbkAppDescription);
	}
	protected function _assignSharePollToView(Survey $survey, $facebookCallbackUrl) {
		$twShareText = "I just answered " . $survey->title ." and earned 19 Snakkle Bucks! Join Snakkle Say.So and earn great prizes!";
		$fbkShareText = 'I just earned 19 Snakkle Bucks for answering the poll "'. $survey->title .'".
Join Snakkle Say.So and get access big giveaways and awesome prizes.';

		$this->_assignShareInfoToView($this->_appShareLink, $twShareText, $fbkShareText, $facebookCallbackUrl, $survey->title, $this->_fbkAppDescription);
	}
	protected function _assignShareQuizToView(Survey $survey, $facebookCallbackUrl) {
		$twShareText = 'I answered a Quiz, "Who is this?"! Join Snakkle Say.So and earn great prizes!';
		$fbkShareText = 'I just answerer a Quiz, "Who is this?".
Join Snakkle Say.So and get access big giveaways and awesome prizes.';

		$this->_assignShareInfoToView($this->_appShareLink, $twShareText, $fbkShareText, $facebookCallbackUrl, "Who is this?", $this->_fbkAppDescription);
	}
}
