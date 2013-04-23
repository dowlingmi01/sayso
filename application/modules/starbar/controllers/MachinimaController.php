<?php

require_once APPLICATION_PATH . '/modules/starbar/controllers/ContentController.php';

class Starbar_MachinimaController extends Starbar_ContentController
{
	protected $_maximumDisplayed = array('polls' => 0, 'surveys' => 0, 'trailers' => 0);

	public function postDispatch() {
		parent::postDispatch();
		if (!$this->_usingJsonPRenderer) {
			$this->view->headLink()->appendStylesheet('/css/starbar-machinima.css');
		}
	}

	protected function _assignShareInfoToView($shareLink = null, $twitterShareText = null, $facebookShareCaption = null, $facebookCallbackUrl = null, $facebookTitle = null, $facebookDescription = null) {
		parent::_assignShareInfoToView($shareLink, $twitterShareText, $facebookShareCaption, $facebookCallbackUrl, $facebookTitle, $facebookDescription);

		$this->view->assign('facebook_share_image_url', 'https://s3.amazonaws.com/say.so/media/misc/ma_FB_Share_Icon_100px.jpg');
	}


	protected $_appShareLink = 'http://Recon.Say.So';
	protected $_fbkAppDescription = "Say.So is your way of making a lasting impact on the communities you love. Participating in Machinima | Recon is easy - by giving your opinion, answering polls and rating Machinima content, you gain points to redeem awesome prizes from Machinima.";
	protected $_fbkOffer = "";//This month Machinima | Recon is giving away 36 prizes of Steam Tokens and Machinima coins. You asked for it, we listened!";

	protected function _assignShareAppToView($facebookCallbackUrl) {
		/*$twAppShareText = 'Join Machinima | Recon and get access to big giveaways and awesome prizes.';*/
		$twAppShareText = 'Checking out Machinima | Recon... Get the Machinima Recon app @';
		$fbkAppShareTitle = 'Checking out Machinima | Recon...';
		/*$fbkAppShareCopy = "I just earned 19 Coins for sharing Machinima | Recon!
Join Machinima | Recon and get access to big giveaways and awesome prizes.";*/
		$fbkAppShareCopy = $this->_fbkOffer;

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
		$surveyTitle = substr_compare($survey->title, '?', -1, 1) === 0 ? substr($survey->title, 0, -1) : $survey->title;
		$shareSurveyTitle = "Machinima | Recon wants to know how I feel about '".$surveyTitle."'";

		$twShareText = "I've finished a Machinima | Recon survey and earned " . $redeemable ." Coins. Get the Machinima | Recon app @ ";

		$fbkShareText = "I just pocketed " . $redeemable ." Coins by giving my opinion and finishing the survey '".$surveyTitle."'";
		$fbkAppDescription = $this->_fbkOffer . "\n" . $this->_fbkAppDescription;

		$this->_assignShareInfoToView($this->_appShareLink, $twShareText, $fbkShareText, $facebookCallbackUrl, $shareSurveyTitle, $fbkAppDescription);
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
		$sharePollTitle = "Machinima | Recon wants to know '".$pollTitle."'";
		$twShareText = "I've finished a Machinima | Recon poll and earned " . $redeemable ." Coins. Get the Machinima | Recon app @ ";
		$fbkShareText = "I just earned " . $redeemable ." Coins for answering the poll '".$pollTitle."'";

		$fbkAppDescription = $this->_fbkOffer . "\n" . $this->_fbkAppDescription;

		$this->_assignShareInfoToView($this->_appShareLink, $twShareText, $fbkShareText, $facebookCallbackUrl, $sharePollTitle , $fbkAppDescription);
	}

	protected function _assignShareTrailerToView(Survey $survey, $facebookCallbackUrl) {

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
		$twShareText = "I've rated Machinima | Recon content and earned " . $redeemable ." Coins. Get the Machinima | Recon app @ ";
		$fbkShareText = "I've rated Machinima | Recon content and earned " . $redeemable ." Coins.";

		$fbkAppDescription = $this->_fbkOffer . "\n" . $this->_fbkAppDescription;

		$this->_assignShareInfoToView($this->_appShareLink, $twShareText, $fbkShareText, $facebookCallbackUrl, $survey->title, $fbkAppDescription);
	}
}
