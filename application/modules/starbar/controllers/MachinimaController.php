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

	public function spotlightAction() {

	}

	public function ptctestAction() {
		printf("Hello world");
	}

	public function trailerAction() {

		$this->_validateRequiredParameters(array('user_id'));

		// this page is fetched via an iframe, not ajax;
		$this->_usingJsonPRenderer = false;

		$request = $this->getRequest();
		$surveyId = (int) abs($request->getParam("survey_id", 0));

		Survey_ResponseCollection::markUnseenSurveysNewForStarbarAndUser($this->starbar_id, $this->user_id, 'trailers', $this->_maximumDisplayed['trailers']);

		$trailers = new SurveyCollection();
		$trailers->loadSurveysForStarbarAndUser($this->starbar_id, $this->user_id, 'trailer', 'new');
		$this->view->trailers = $trailers;

		$infoForTrailers = new Survey_TrailerInfoCollection();
		$infoForTrailers->getTrailerInfoForTrailers($trailers);
		// re-index the trailer info by survey_id
		$infoForTrailersIndexedArray = array();
		foreach($infoForTrailers as $trailerInfo) {
			$infoForTrailersIndexedArray[$trailerInfo->survey_id] = $trailerInfo;
		}
		$this->view->info_for_trailers = $infoForTrailersIndexedArray;

		$currentTrailer = null;
		if ($surveyId && isset($trailers[$surveyId])) {
			$currentTrailer = $trailers[$surveyId];
		} elseif ($trailers) {
			foreach ($trailers as $trailer) {
				$currentTrailer = $trailer;
				break;
			}
		}

		if ($currentTrailer) {
			$activeSurveyTrailerInfoId = $infoForTrailersIndexedArray[$currentTrailer->id]->id;
			$this->view->current_trailer = $currentTrailer;

			$firstQuestion = new Survey_Question();
			$firstQuestion->loadDataByUniqueFields(array('survey_id' => $currentTrailer->id, 'ordinal' => 1, 'survey_trailer_info_id'=>$activeSurveyTrailerInfoId));

			$this->view->first_question = $firstQuestion;

			$firstQuestionChoices = new Survey_QuestionChoiceCollection();
			$firstQuestionChoices->loadAllChoicesForSurveyQuestion($firstQuestion->id);

			$this->view->first_question_choices = $firstQuestionChoices;

			$secondQuestion = new Survey_Question();
			$secondQuestion->loadDataByUniqueFields(array('survey_id' => $currentTrailer->id, 'ordinal' => 2, 'survey_trailer_info_id'=>$activeSurveyTrailerInfoId));

			$this->view->second_question = $secondQuestion;

			$secondQuestionChoices = new Survey_QuestionChoiceCollection();
			$secondQuestionChoices->loadAllChoicesForSurveyQuestion($secondQuestion->id);

			$this->view->second_question_choices = $secondQuestionChoices;

			$facebookCallbackUrl = "https://".BASE_DOMAIN."/starbar/content/facebook-post-result?shared_type=trailer&shared_id=".$currentTrailer->id."&user_id=".$this->user_id."&user_key=".$this->user_key."&starbar_id=".$this->starbar_id;
			$this->_assignShareTrailerToView($currentTrailer, $facebookCallbackUrl);
		}

		$this->view->user_id = $this->user_id;
		$this->view->user_key = $this->user_key;
		$this->view->starbar_id = $this->starbar_id;
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
