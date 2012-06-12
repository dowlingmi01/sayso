<?php

require_once APPLICATION_PATH . '/modules/starbar/controllers/ContentController.php';

class Starbar_MachinimaController extends Starbar_ContentController
{
	public function postDispatch() {
		parent::postDispatch();
		if (!$this->_usingJsonPRenderer) {
			$this->view->headLink()->appendStylesheet('/css/starbar-machinima.css');
		}
	}

	protected function _assignShareInfoToView($shareLink = null, $twitterShareText = null, $facebookShareCaption = null, $facebookCallbackUrl = null, $facebookTitle = null, $facebookDescription = null) {
		parent::_assignShareInfoToView($shareLink, $twitterShareText, $facebookShareCaption, $facebookCallbackUrl, $facebookTitle, $facebookDescription);
		$this->view->assign('facebook_share_image_url', 'https://s3.amazonaws.com/say.so/media/snakkle/logo_snakkle.png');
	}
	protected $_appShareLink = 'http://Snakkle.Say.So';
	protected $_fbkAppDescription = "Say.So is your way of making a lasting impact on the communities you love. Participating in Snakkle Say.So is easy - by giving your opinion, answering polls and taking fun quizzes, you gain points to redeem awesome prizes from Snakkle.";

	protected function _assignShareAppToView($facebookCallbackUrl) {
		$twAppShareText = 'Join Snakkle Say.So and get access to big giveaways and great prizes from Snakkle.';
		$fbkAppShareTitle = 'Snakkle Say.So';
		$fbkAppShareCopy = "I just earned 19 Snakkle Bucks for sharing Snakkle Say.So!
Join Snakkle Say.So and get access to big giveaways and awesome prizes.";

		$this->_assignShareInfoToView($this->_appShareLink, $twAppShareText, $fbkAppShareCopy,  $facebookCallbackUrl, $fbkAppShareTitle, $this->_fbkAppDescription);
	}
	protected function _assignShareSurveyToView(Survey $survey, $completed, $facebookCallbackUrl) {
		switch ($survey->reward_category) {
			case "premium":
				$experience = ($completed ? 5000 : 1000);
				$redeemable = ($completed ? 375 : 75);
				break;
			case "profile":
				$experience = ($completed ? 2000 : 500);
				$redeemable = ($completed ? 150 : 38);
				break;
			case "standard":
			default:
				$experience = ($completed ? 500 : 250);
				$redeemable = ($completed ? 38 : 19);
				break;
		}
		$twShareText = 'I just answered the survey "' . $survey->title .'" and earned ' . $redeemable . " Snakkle Bucks! Join Snakkle Say.So and earn great prizes!";
		$fbkShareText = 'I just earned ' . $redeemable . ' Snakkle Bucks for answering the survey "'. $survey->title .'".
Join Snakkle Say.So and get access to big giveaways and awesome prizes.';

		$this->_assignShareInfoToView($this->_appShareLink, $twShareText, $fbkShareText, $facebookCallbackUrl, $survey->title, $this->_fbkAppDescription);
	}
	protected function _assignSharePollToView(Survey $survey, $facebookCallbackUrl) {
		switch ($survey->reward_category) {
			case "premium":
				$experience = 500;
				$redeemable = 38;
				break;
			case "profile":
			case "standard":
			default:
				$experience = 250;
				$redeemable = 19;
				break;
		}
		$pollTitle = substr_compare($survey->title, '?', -1, 1) === 0 ? substr($survey->title, 0, -1) : $survey->title;
		$twShareText = 'I just answered the poll "' . $pollTitle .'" and earned ' . $redeemable . " Snakkle Bucks! Join Snakkle Say.So and earn great prizes!";
		$fbkShareText = 'I just earned ' . $redeemable . ' Snakkle Bucks for answering the poll "'. $pollTitle .'".
Join Snakkle Say.So and get access to big giveaways and awesome prizes.';

		$this->_assignShareInfoToView($this->_appShareLink, $twShareText, $fbkShareText, $facebookCallbackUrl, $survey->title, $this->_fbkAppDescription);
	}
	protected function _assignShareQuizToView(Survey $survey, $facebookCallbackUrl) {
		$twShareText = 'I just answered a Snakkle Celebrity Quiz! Join Snakkle Say.So and earn great prizes!';
		$fbkShareText = 'I just answered a Snakkle Celebrity Quiz!
Join Snakkle Say.So and get access to big giveaways and awesome prizes.';

		$this->_assignShareInfoToView($this->_appShareLink, $twShareText, $fbkShareText, $facebookCallbackUrl, "Who is this?", $this->_fbkAppDescription);
	}
}