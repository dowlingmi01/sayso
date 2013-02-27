<?php

require_once APPLICATION_PATH . '/modules/starbar/controllers/ContentController.php';

class Starbar_MovieController extends Starbar_ContentController
{
	protected $_maximumDisplayed = array('polls' => 0, 'surveys' => 0, 'trailers' => 0);

	public function userProfileAction () {
		Survey_ResponseCollection::markUnseenSurveysNewForStarbarAndUser($this->starbar_id, $this->user_id, 'trailers', $this->_maximumDisplayed['trailers']);
		$this->_assignSurveysToView('trailers');
		parent::userProfileAction();
	}

	protected function _assignShareInfoToView($shareLink = null, $twitterShareText = null, $facebookShareCaption = null, $facebookCallbackUrl = null, $facebookTitle = null, $facebookDescription = null) {
		parent::_assignShareInfoToView($shareLink, $twitterShareText, $facebookShareCaption, $facebookCallbackUrl, $facebookTitle, $facebookDescription);
		$this->view->assign('facebook_share_image_url', 'https://s3.amazonaws.com/say.so/media/misc/mo_FB_Share_Icon_100px.jpg');
	}

	protected $_appShareLink = 'http://Movie.Say.So';
	protected $_fbkAppDescription = "Say.So is your way of making a lasting impact on the community you love (and showing off how much you know about movies). Participating in Movie Say.So is simple - give your opinion, gain points, redeem awesome prizes for movie buffs.";

	protected function _assignShareAppToView($facebookCallbackUrl) {
		$twAppShareText = "I'm cementing my position as the ultimate film guru and earning some sweet movie swag. Show off your knowledge too. ";
		$fbkAppShareTitle = 'Movie Say.So';
		$fbkAppShareCopy = "I'm cementing my position as the ultimate film guru and earning some sweet movie swag. Try showing off your knowledge too";

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
		$shareSurveyTitle = $surveyTitle;
		$twShareText = "I'm finally putting my random movie knowledge to good use. Another " . $redeemable ." Cinebucks, another step closer to swag! Movie Say.So app @";
		$fbkShareText = "I'm finally putting my random movie knowledge to good use. Another  " . $redeemable ."  Cinebucks, another step closer to swag from http://movie.say.so";


		$this->_assignShareInfoToView($this->_appShareLink, $twShareText, $fbkShareText, $facebookCallbackUrl, $shareSurveyTitle, $this->_fbkAppDescription);
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
		$sharePollTitle = $pollTitle;
		$fbkShareText = "I've had my say, what do you think? '".$pollTitle."'";;
		$twShareText = "I'm another ".$redeemable." Cinebucks closer to more movie loot! Join Movie Say.So and earn great prizes! @";

		$this->_assignShareInfoToView($this->_appShareLink, $twShareText, $fbkShareText, $facebookCallbackUrl, $sharePollTitle, $this->_fbkAppDescription);
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
		$twShareText = "Check out this quiz at Movie Say.So! I did and now I'm on my way to earning great prizes! @";
		$fbkShareText = "Check out this quiz on ".$survey->title." at Movie Say.So! I did and now I'm on my way to earning great prizes! http://movie.say.so";

		$this->_assignShareInfoToView($this->_appShareLink, $twShareText, $fbkShareText, $facebookCallbackUrl, $survey->title, $this->_fbkAppDescription);
	}
}
