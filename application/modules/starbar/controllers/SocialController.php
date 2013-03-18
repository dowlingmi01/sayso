<?php

require_once APPLICATION_PATH . '/modules/starbar/controllers/ContentController.php';

class Starbar_SocialController extends Starbar_ContentController
{
	protected $_maximumDisplayed = array('polls' => 0, 'surveys' => 0, 'trailers' => 0);

	public function rewardsAction () {
		parent::rewardsAction();
		// Filter out sold out items
		foreach($this->view->rewards as $good) {
			if ($good->inventory_sold >= $good->inventory_total) {
				$this->view->rewards->removeItem($good->id);
			}
		}
	}

	public function userProfileAction () {
		Survey_ResponseCollection::markUnseenSurveysNewForStarbarAndUser($this->starbar_id, $this->user_id, 'trailers', $this->_maximumDisplayed['trailers']);
		$this->_assignSurveysToView('trailers');
		parent::userProfileAction();
	}

	protected function _assignShareInfoToView($shareLink = null, $twitterShareText = null, $facebookShareCaption = null, $facebookCallbackUrl = null, $facebookTitle = null, $facebookDescription = null) {
		parent::_assignShareInfoToView($shareLink, $twitterShareText, $facebookShareCaption, $facebookCallbackUrl, $facebookTitle, $facebookDescription);
		$this->view->assign('facebook_share_image_url', 'https://s3.amazonaws.com/say.so/media/misc/so_FB_Share_Icon_100px.jpg');
	}

	protected $_appShareLink = 'http://Social.Say.So';
	protected $_fbkAppDescription = "Say.So is your way of making a lasting impact on the communities you love. Participating in Social Say.So is easy - by giving your opinion, answering polls and finishing Social Say.So missions, you gain points to redeem awesome prizes from Say.So.";

	protected function _assignShareAppToView($facebookCallbackUrl) {
		$twAppShareText = 'Checking out Social Say.So... Get the Social Say.So app @';
		$fbkAppShareTitle = 'Social Say.So';
		$fbkAppShareCopy = "Checking out Social Say.So...";

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
		$twShareText = "I've finished a Social Say.So survey and earned " . $redeemable ." PaySos. Get the Social Say.So app @";
		$fbkShareText = "I just pocketed " . $redeemable ." PaySos by giving my opinion and finishing the survey '".$survey->title."'.";

		$shareTitle = "Social Say.So wants to know how I feel about '".$survey->title."'";
		$this->_assignShareInfoToView($this->_appShareLink, $twShareText, $fbkShareText, $facebookCallbackUrl, $shareTitle, $this->_fbkAppDescription);
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

		$shareTitle = "Social Say.So wants to know '".$survey->title."'";
		$twShareText = "I've finished a Social Say.So poll and earned " . $redeemable . " PaySos. Get the Social Say.So app @";
		$fbkShareText = "I just earned " . $redeemable ." PaySos for answering the poll '".$survey->title."'";

		$this->_assignShareInfoToView($this->_appShareLink, $twShareText, $fbkShareText, $facebookCallbackUrl, $shareTitle, $this->_fbkAppDescription);
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
		$twShareText = "I've finished a Social Say.So trailer and earned " . $redeemable ." PaySos. Get the Social Say.So app @";
		$fbkShareText = "I've finished a Social Say.So trailer and earned " . $redeemable ." PaySos. Get the Social Say.So app @ http://Social.Say.So";

		$this->_assignShareInfoToView($this->_appShareLink, $twShareText, $fbkShareText, $facebookCallbackUrl, $survey->title, $this->_fbkAppDescription);
	}
}
