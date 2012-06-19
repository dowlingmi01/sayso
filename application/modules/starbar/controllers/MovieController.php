<?php

require_once APPLICATION_PATH . '/modules/starbar/controllers/ContentController.php';

class Starbar_MovieController extends Starbar_ContentController
{
	public function postDispatch() {
		parent::postDispatch();
		if (!$this->_usingJsonPRenderer) {
			$this->view->headLink()->appendStylesheet('/css/starbar-movie.css');
		}
	}

	public function spotlightAction() {

	}

	protected function _assignShareInfoToView($shareLink = null, $twitterShareText = null, $facebookShareCaption = null, $facebookCallbackUrl = null, $facebookTitle = null, $facebookDescription = null) {
		parent::_assignShareInfoToView($shareLink, $twitterShareText, $facebookShareCaption, $facebookCallbackUrl, $facebookTitle, $facebookDescription);
		$this->view->assign('facebook_share_image_url', 'https://s3.amazonaws.com/say.so/media/moviebar/logo_moviebar.png');
	}
	protected $_appShareLink = 'http://movie.say.so';
	protected $_fbkAppDescription = "Say.So is your way of making a lasting impact on the communities you love. Participating in Movie Say.So is easy - by giving your opinion, answering polls and rating new and retro movie trailers, you gain points to redeem awesome prizes for movie buffs.";

	protected function _assignShareAppToView($facebookCallbackUrl) {
		$twAppShareText = 'Join Movie Say.So and get access to big giveaways and awesome prizes. Movie.Say.So';
		$fbkAppShareTitle = 'Movie Say.So';
		$fbkAppShareCopy = "I just earned 19 CineBucks for sharing Movie Say.So!
Join Movie Say.So and get access to big giveaways and awesome prizes.";

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
		$twShareText = "I just answered " . $survey->title ." and earned " . $redeemable . " CineBucks! Join Movie Say.So and earn great prizes!";
		$fbkShareText = 'I just earned ' . $redeemable . ' CineBucks for answering the survey "'. $survey->title .'".
Join Movie Say.So and get access big giveaways and awesome prizes.';

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
		$twShareText = "I just answered " . $survey->title ." and earned " . $redeemable . " CineBucks! Join Movie Say.So and earn great prizes! Movie.Say.So";
		$fbkShareText = 'I just earned ' . $redeemable . ' CineBucks for answering the poll "'. $survey->title .'".
Join Movie Say.So and get access to big giveaways and awesome prizes.';

		$this->_assignShareInfoToView($this->_appShareLink, $twShareText, $fbkShareText, $facebookCallbackUrl, $survey->title, $this->_fbkAppDescription);
	}
	protected function _assignShareQuizToView(Survey $survey, $facebookCallbackUrl) {
		$twShareText = 'I answered a Quiz, "'.$survey->title.'"! Join Movie Say.So and earn great prizes!';
		$fbkShareText = 'I just answered a Quiz, "'.$survey->title.'"!
Join Movie Say.So and get access big giveaways and awesome prizes.';

		$this->_assignShareInfoToView($this->_appShareLink, $twShareText, $fbkShareText, $facebookCallbackUrl, "Who is this?", $this->_fbkAppDescription);
	}
}
