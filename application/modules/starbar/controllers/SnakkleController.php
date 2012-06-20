<?php

require_once APPLICATION_PATH . '/modules/starbar/controllers/ContentController.php';

class Starbar_SnakkleController extends Starbar_ContentController
{
	protected $_maximumDisplayed = array('polls' => 0, 'surveys' => 0, 'quizzes' => 0);

	public function postDispatch() {
		parent::postDispatch();
		if (!$this->_usingJsonPRenderer) {
			$this->view->headLink()->appendStylesheet('/css/starbar-snakkle.css');
		}
	}

	public function userProfileAction () {
		Survey_ResponseCollection::markUnseenSurveysNewForStarbarAndUser($this->starbar_id, $this->user_id, 'quizzes', $this->_maximumDisplayed['quizzes']);
		$this->_assignSurveysToView('quizzes');
		parent::userProfileAction();
	}

	// Fetches the next quiz for the current user for display
	// Optionally takes quiz_index as CGI parameter to indicate getting the
	// (n+1)th quiz for the user (0 = first quiz, 1 = second quiz, etc.)
	public function quizAction ()
	{
		$this->_validateRequiredParameters(array('user_id'));

		$request = $this->getRequest();
		$quizIndex = (int) abs($request->getParam("quiz_index", 0));

		Survey_ResponseCollection::markUnseenSurveysNewForStarbarAndUser($this->starbar_id, $this->user_id, 'quizzes', $this->_maximumDisplayed['quizzes']);

		$quizzesById = new SurveyCollection();
		$quizzesById->loadSurveysForStarbarAndUser($this->starbar_id, $this->user_id, 'quiz', 'new');

		$quizzes = array();

		$this->view->user_id = $this->user_id;
		$this->view->user_key = $this->user_key;
		$this->view->starbar_id = $this->starbar_id;
		$this->view->quiz_index = $quizIndex;

		foreach ($quizzesById as $quiz) {
			$quizzes[] = $quiz;
		}

		if (($quizIndex <= sizeof($quizzes) - 1) && isset($quizzes[$quizIndex])) {
			$quiz = $quizzes[$quizIndex];
			$quizQuestion = new Survey_Question();
			$quizQuestion->loadDataByUniqueFields(array('survey_id' => $quiz->id));
			$quizChoices = new Survey_QuestionChoiceCollection();
			$quizChoices->loadAllChoicesForSurveyQuestion($quizQuestion->id);
			$quizResults = array();

			$totalQuizResponses = 0;
			foreach ($quizChoices as $quizChoice) {
				$numberOfResponsesForThisChoice = Survey_QuestionChoice::getNumberOfResponsesForChoice($quizChoice->id);
				$quizResults[$quizChoice->id] = $numberOfResponsesForThisChoice;
				$totalQuizResponses += $numberOfResponsesForThisChoice;
			}

			if ($quizQuestion->title && $quizQuestion->title != $quiz->title)
				$this->view->quiz_hint = $quizQuestion->title;
			else $this->view->quiz_hint = "";

			$this->view->quiz = $quiz;
			$this->view->quiz_question = $quizQuestion;
			$this->view->quiz_choices = $quizChoices;
			$this->view->quiz_results = $quizResults;
			$this->view->total_quiz_responses = $totalQuizResponses;

			$facebookCallbackUrl = "https://".BASE_DOMAIN."/starbar/content/facebook-post-result?shared_type=quiz&shared_id=".$quiz->id."&user_id=".$this->user_id."&user_key=".$this->user_key."&starbar_id=".$this->starbar_id;
			$this->_assignShareQuizToView($quiz, $facebookCallbackUrl);
		}
		else $this->view->quiz = false;
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
