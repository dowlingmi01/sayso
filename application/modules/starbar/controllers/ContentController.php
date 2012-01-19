<?php

require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Starbar_ContentController extends Api_GlobalController
{
	// Render with JsonP by default
	protected $_usingJsonPRenderer = true;

	// To be set by inherited classes, e.g. HellomusicController
	protected $_maximumDisplayed = array('polls' => 0, 'surveys' => 0);

	public function preDispatch()
	{
		try {
			$this->_validateRequiredParameters(array('user_id', 'user_key', 'auth_key'));
		} catch (Exception $e) {
			if ($e->getCode() == Api_Error::MISSING_PARAMETERS) {
				echo "This page can only be loaded via the app.";
			}
			exit;
		}
	}

	public function postDispatch()
	{
		if ($this->_usingJsonPRenderer) {
			$this->_enableRenderer(new Api_Plugin_JsonPRenderer());
			$this->render();
			return $this->_resultType(new Object(array('html' => $this->getResponse()->getBody())));
		} else {
			// iframe loaded content, hence the need for all JS dependencies
			$this->view->headScript()->appendFile('/js/starbar/jquery-1.7.1.min.js');
			$this->view->headScript()->appendFile('/js/starbar/jquery-ui-1.8.16.custom.min.js');
			$this->view->headScript()->appendFile('/js/starbar/jquery.jscrollpane.min.js');
			$this->view->headScript()->appendFile('/js/starbar/jquery.cookie.js');
			$this->view->headScript()->appendFile('/js/starbar/jquery.jeip.js');
			$this->view->headScript()->appendFile('/js/starbar/jquery.cycle.lite.js');
			$this->view->headLink()->appendStylesheet('/css/starbar-generic.css');
			// For init-remote.phtml
			$this->_assignStarbarToView();
		}
	}

	public function rewardsAction ()
	{
		$gamer = Game_Starbar::getInstance()->getGamer();
		$goods = Api_Adapter::getInstance()->call('Gaming', 'getGoodsFromStore');
		$sortedGoods = Api_GamingController::prepareGoodsForGamer($goods, $gamer);

		$request = $this->getRequest();
		$this->view->assign('chosen_good_id', $request->getParam('chosen_good_id'));

		$this->view->rewards = $sortedGoods;
	}

	/**
	 * Get reward redemption view
	 *
	 * @throws Api_Exception
	 */
	public function rewardRedeemAction () {
		$good = Api_Adapter::getInstance()->call('Gaming', 'getGoodFromStore');

		$user = Api_UserSession::getInstance($this->user_key)->getUser();

		$userAddress = $user->getPrimaryAddress();

		$this->view->assign(array('good' => $good, 'game' => Game_Starbar::getInstance(), 'user' => $user, 'user_address' => $userAddress));
		return $this->_resultType($good);
	}

	public function rewardRedeemedAction () {

		$this->_validateRequiredParameters(array('quantity', 'good_id', 'user_key'));

		$good = Api_Adapter::getInstance()->call('Gaming', 'getGoodFromStore');
		/* @var $good Gaming_BigDoor_Good */

		$game = Game_Starbar::getInstance();
		$game->purchaseGood($good, $this->quantity);

		$user = new User();
		$user->loadData($this->user_id);

		if (isset($this->order_first_name)) {
			// shippable item
			// validation done in JS

			$userAddress = new User_Address();
			if ($user->primary_address_id) {
				$userAddress->loadData($user->primary_address_id);
			} else {
				$userAddress->user_id = $this->user_id;
			}

			$userAddress->street1 = $this->order_address_1;
			$userAddress->street2 = $this->order_address_2;
			$userAddress->locality = $this->order_city;
			$userAddress->region = $this->order_state;
			$userAddress->postalCode = $this->order_zip;
			$userAddress->country = $this->order_country;
			$userAddress->save();

			if (!$user->primary_address_id) {
				$user->primary_address_id = $userAddress->id;
			}

			$user->first_name = $this->order_first_name;
			$user->last_name = $this->order_last_name;
			$user->save();


			try {
				$userEmail = new User_Email();
				$userEmail->loadData($user->primary_email_id);
				$message = '
					Say.So Music Bar redemption made for ' . $good->title . '

					Order Details
					=============
					First Name: ' . $this->order_first_name . '
					Last Name: ' . $this->order_last_name . '
					Street Address 1: ' . $this->order_address_1 . '
					Street Address 2: ' . $this->order_address_2 . '
					City: ' . $this->order_city . '
					State/Region: ' . $this->order_state . '
					Postal Code: ' . $this->order_zip . '
					Country: ' . $this->order_country . '

					User ID: ' . $this->user_id . '
					User Email: ' . $userEmail->email . '
					=============
					Thank you,
					Say.So Mailer v3.4
				';

				$config = Api_Registry::getConfig();
				$mail = new Mailer();
				$mail->setFrom('hmorders@say.so')
					 ->addTo('hmorders@say.so')
					 ->setSubject('Redemption');
				$mail->setBodyMultilineText($message);
				$mail->send(new Zend_Mail_Transport_Smtp());
			} catch (Exception $e) {
				quickLog($message);
			}
		} else {

		}
		$this->view->assign(array('game' => $game, 'good' => $good, 'user' => $user, 'user_address' => $userAddress));
		return $this->_resultType($good);
	}

	public function aboutSaysoAction ()
	{
		$this->view->assign('show_testing_function', in_array(APPLICATION_ENV, array('development', 'sandbox', 'testing')));
	}

	// Embed a single SG poll. Expects "survey_id" passed via URL (GET)
	public function embedPollAction ()
	{
		$this->_validateRequiredParameters(array('survey_id'));
		// this page is fetched via an iframe, not ajax;
		$this->_usingJsonPRenderer = false;

		$survey = new Survey();
		$survey->loadData($this->survey_id);

		$this->view->assign('survey', $survey);

		$bundleOfJoy = $this->_getBundleOfJoy($this->survey_id);
		$this->view->assign('bundle_of_joy', $bundleOfJoy);

		$this->view->user_id = $this->user_id;
		$this->view->user_key = $this->user_key;
		$this->view->auth_key = $this->auth_key;

		// @todo point this to onboarding
		$shareLink = "http://music.say.so/";

		$shareText = "Poll time! Just took the '".$survey->title."' poll on the Say.So Music Bar";
		$facebookTitle = $survey->title;
		$facebookDescription = "Like Music? You can get the Say.So Music Bar from Hello Music, give your opinion, earn points, get FREE gear, as well as exclusive access to deeply discounted music gear.";
		$facebookCallbackUrl = "https://".BASE_DOMAIN."/starbar/content/facebook-post-result?shared_type=poll&shared_id=".$survey->id."&user_id=".$this->user_id."&user_key=".$this->user_key."&auth_key=".$this->auth_key;
		$this->_assignShareInfoToView($shareLink, $shareText, $shareText, $facebookCallbackUrl, $facebookTitle, $facebookDescription);
	}

	// Embed a single SG survey. Expects "survey_id" passed via URL (GET)
	public function embedSurveyAction ()
	{
		$this->_validateRequiredParameters(array('survey_id', 'user_id'));

		$survey = new Survey();
		$survey->loadData($this->survey_id);

		$this->view->assign('survey', $survey);

		$surveyUserMap = new Survey_UserMap();
		$surveyAlreadyCompleted = $surveyUserMap->checkIfUserHasCompletedSurvey($this->user_id, $this->survey_id);

		if ($surveyAlreadyCompleted) {
			$this->view->assign('survey_already_completed', true);
		} else {
			$this->view->assign('survey_already_completed', false);

			// Find the next survey after this survey for this user
			$nextSurvey = Survey::getNextSurveyForUser($survey, $this->user_id);
			if ($nextSurvey->id) $nextSurveyId = $nextSurvey->id;
			else $nextSurveyId = -1;

			$this->view->assign('next_survey_id', $nextSurveyId);

			$bundleOfJoy = $this->_getBundleOfJoy($this->survey_id, $nextSurveyId);
			$this->view->assign('bundle_of_joy', $bundleOfJoy);
		}

	}

	public function surveyUnavailableAction ()
	{
		// this page is fetched via an iframe, not ajax;
		$this->_usingJsonPRenderer = false;
	}

	public function surveyDisqualifyAction ()
	{
		$this->_replaceBundleOfJoyWithGetVariables();
		$this->_validateRequiredParameters(array('survey_id', 'next_survey_id'));
		// this page is fetched via an iframe, not ajax;
		$this->_usingJsonPRenderer = false;

		$survey = new Survey();
		$survey->loadData($this->survey_id);

		$nextSurvey = new Survey();
		if ($this->next_survey_id != -1) {
			$nextSurvey->loadData($this->next_survey_id);
		}

		$this->view->assign('survey', $survey);
		$this->view->assign('next_survey', $nextSurvey);

		// @todo point this to onboarding
		$shareLink = "http://music.say.so/";
		// @todo share text to vary based on starbar_id?
		$shareText = "Survey time! Just filled out '".$survey->title."' on the Say.So Music Bar";
		$facebookTitle = $survey->title;
		$facebookDescription = "Like Music? You can get the Say.So Music Bar from Hello Music, give your opinion, earn points, get FREE gear, as well as exclusive access to deeply discounted music gear.";
		$facebookCallbackUrl = "https://".BASE_DOMAIN."/starbar/content/facebook-post-result?shared_type=survey&shared_id=".$survey->id."&user_id=".$this->user_id."&user_key=".$this->user_key."&auth_key=".$this->auth_key;
		$this->_assignShareInfoToView($shareLink, $shareText, $shareText, $facebookCallbackUrl, $facebookTitle, $facebookDescription);
	}

	public function surveyCompleteAction ()
	{
		$this->_replaceBundleOfJoyWithGetVariables();
		$this->_validateRequiredParameters(array('survey_id', 'next_survey_id'));
		// this page is fetched via an iframe, not ajax;
		$this->_usingJsonPRenderer = false;

		$survey = new Survey();
		$survey->loadData($this->survey_id);

		$nextSurvey = new Survey();
		if ($this->next_survey_id != -1) {
			$nextSurvey->loadData($this->next_survey_id);
		}

		$this->view->assign('survey', $survey);
		$this->view->assign('next_survey', $nextSurvey);

		// @todo point this to onboarding
		$shareLink = "http://music.say.so/";
		// @todo share text to vary based on starbar_id?
		$shareText = "Survey time! Just filled out '".$survey->title."' on the Say.So Music Bar";
		$facebookTitle = $survey->title;
		$facebookDescription = "Like Music? You can get the Say.So Music Bar from Hello Music, give your opinion, earn points, get FREE gear, as well as exclusive access to deeply discounted music gear.";
		$facebookCallbackUrl = "https://".BASE_DOMAIN."/starbar/content/facebook-post-result?shared_type=survey&shared_id=".$survey->id."&user_id=".$this->user_id."&user_key=".$this->user_key."&auth_key=".$this->auth_key;
		$this->_assignShareInfoToView($shareLink, $shareText, $shareText, $facebookCallbackUrl, $facebookTitle, $facebookDescription);
	}

	public function surveyRedirectAction ()
	{
		$this->_validateRequiredParameters(array('survey_id', 'next_survey_id'));
		// this page is fetched via an iframe, not ajax;
		$this->_usingJsonPRenderer = false;

		$survey = new Survey();
		$survey->loadData($this->survey_id);

		$bundleOfJoy = $this->_getBundleOfJoy($this->survey_id, $this->next_survey_id);
		$redirectAddress = ($_SERVER['HTTPS'] ? 'https:' : 'http:')
							. "//www.surveygizmo.com/s3/"
							. $survey->external_id . "/" . $survey->external_key
							. "?bundle_of_joy=" . $bundleOfJoy;
		$this->_redirect($redirectAddress);
	}

	// Fetches polls for the current user for display
	public function pollsAction ()
	{
		$surveyUserMaps = new Survey_UserMapCollection();
		$surveyUserMaps->markOldSurveysArchivedForStarbarAndUser($this->starbar_id, $this->user_id, 'polls');
		$surveyUserMaps->markUnseenSurveysNewForStarbarAndUser($this->starbar_id, $this->user_id, 'polls', $this->_maximumDisplayed['polls']);
		$this->_assignSurveysToView('polls');
	}

	// Fetches surveys for the current user for display
	public function surveysAction ()
	{
		$surveyUserMaps = new Survey_UserMapCollection();
		$surveyUserMaps->markOldSurveysArchivedForStarbarAndUser($this->starbar_id, $this->user_id, 'surveys');
		$surveyUserMaps->markUnseenSurveysNewForStarbarAndUser($this->starbar_id, $this->user_id, 'surveys', $this->_maximumDisplayed['surveys']);
		$this->_assignSurveysToView('surveys');
	}

	public function onboardingAction ()
	{

	}

	public function promosAction ()
	{

	}

	public function userProfileAction ()
	{
		$user = new User();
		$user->loadData($this->user_id);
		$this->view->assign('user', $user);

		$facebookSocial = new User_Social();
		$facebookSocial->loadByUserIdAndProvider($user->id, 'facebook');
		$this->view->assign('facebook_social', $facebookSocial);

		$twitterSocial = new User_Social();
		$twitterSocial->loadByUserIdAndProvider($user->id, 'twitter');
		$this->view->assign('twitter_social', $twitterSocial);

		$userEmail = new User_Email();
		$userEmail->loadData($user->primary_email_id);
		$this->view->assign('user_email', $userEmail);

		// Assign the counts for surveys and polls
		$surveyUserMaps = new Survey_UserMapCollection();
		$surveyUserMaps->markUnseenSurveysNewForStarbarAndUser($this->starbar_id, $this->user_id, 'polls', $this->_maximumDisplayed['polls']);
		$this->_assignSurveysToView('polls');
		$surveyUserMaps->markUnseenSurveysNewForStarbarAndUser($this->starbar_id, $this->user_id, 'surveys', $this->_maximumDisplayed['surveys']);
		$this->_assignSurveysToView('surveys');

		// @todo point this to onboarding
		$shareLink = "http://music.say.so/";
		// @todo share text to vary based on starbar_id?
		$twitterShareText = "Join me in the Say.So Music Bar app. Get access to sweet gear deals and a chance to win a Takamine Guitar";
		$facebookTitle = "Say.So Music Bar";
		$facebookCaption = "If you're a Musician or dig music gear, you should join me in the Say.So Music Bar from Hello Music. We get access to some sweet gear deals and get awesome odds on walking away with one of their big giveaways like a Takamine Acoustic, a Full Midi Kit, and others. We just give our opinion on a few things and they give us Notes we can redeem for stuff. Sweet deal. Only lasts a month. Want in?";
		$facebookCallbackUrl = "https://".BASE_DOMAIN."/starbar/content/facebook-post-result?shared_type=starbar&shared_id=".$this->starbar_id."&user_id=".$this->user_id."&user_key=".$this->user_key."&auth_key=".$this->auth_key;
		$this->_assignShareInfoToView($shareLink, $twitterShareText, $facebookCaption,  $facebookCallbackUrl, $facebookTitle, null);
	}

	public function userShareAction()
	{

	}

	public function userLevelAction ()
	{

	}

	public function facebookConnectAction ()
	{
		// this page is fetched in a popup, not ajax
		$this->_usingJsonPRenderer = false;

		$config = Api_Registry::getConfig();

		$facebook = new Facebook(array(
			'appId'  => $config->facebook->app_id,
			'secret' => $config->facebook->secret
		));

		$fbUser = $facebook->getUser();

		if ($fbUser) {
			try {
				$fbProfile = $facebook->api('/me');
			} catch (FacebookApiException $e) {
				error_log($e);
				$fbUser = null;
			}
		}

		if (in_array(APPLICATION_ENV, array('development', 'sandbox', 'testing'))) {
			$callbackUrl = "http://".BASE_DOMAIN."/starbar/content/facebook-connect?user_id=".$this->user_id."&user_key=".$this->user_key;
		} else {
			$callbackUrl = "https://".BASE_DOMAIN."/starbar/content/facebook-connect?user_id=".$this->user_id."&user_key=".$this->user_key;
		}

		if ($fbUser && $this->user_key && (int)$this->user_id === (int)Api_UserSession::getInstance($this->user_key)->getId()) {
			$userSocial = new User_Social();
			$userSocial->user_id = $this->user_id;
			$userSocial->provider = "facebook";
			$userSocial->identifier = $fbUser;
			$userSocial->save();

			if (isset($fbProfile['first_name'])) {
				$user = new User();
				$user->loadData($this->user_id);
				if (!$user->username) {
					$user->username = $fbProfile['first_name'];
					$user->save();
				}
			}

			Game_Starbar::getInstance()->associateSocialNetwork($userSocial);

			// Show user congrats notification
			$message = new Notification_Message();
			$message->loadDataByUniqueFields(array('short_name' => 'FB Account Connected'));

			if ($message->id) {
				$messageUserMap = new Notification_MessageUserMap();
				$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($message->id, $this->user_id, false);
			}
			$this->_redirect('/starbar/content/close-window?user_id='.$this->user_id.'&user_key='.$this->user_key.'&auth_key='.$this->auth_key);
		} else {
			$this->_redirect($facebook->getLoginUrl());
		}
	}

	public function twitterConnectRedirectAction ()
	{
		$config = Api_Registry::getConfig();

		$success = false;

		try {
			/* Build TwitterOAuth object with client credentials. */
			$connection = new TwitterOAuth($config->twitter->consumer_key, $config->twitter->consumer_secret);

			$callbackUrl = 'https://'.BASE_DOMAIN.'/starbar/content/twitter-connect-result?user_id='.$this->user_id.'&user_key='.$this->user_key.'&auth_key='.$this->auth_key;

			/* Get temporary credentials and set the callback URL. */
			$twitterRequestToken = $connection->getRequestToken($callbackUrl);

			/* Save temporary credentials to session. */
			$_SESSION['oauth_token'] = $twitterRequestToken['oauth_token'];
			$_SESSION['oauth_token_secret'] = $twitterRequestToken['oauth_token_secret'];

			if ($twitterRequestToken['oauth_callback_confirmed'] == 'true') $success = true;
		} catch (Exception $e) {}

		if ($success) {
			$this->_redirect("https://api.twitter.com/oauth/authorize?oauth_token=".$twitterRequestToken['oauth_token']);
		} else {
			$this->_redirect('/starbar/content/twitter-fail?user_id='.$this->user_id.'&user_key='.$this->user_key.'&auth_key='.$this->auth_key);
		}
	}

	public function twitterConnectResultAction ()
	{
		// this page is fetched in a popup, not ajax
		$this->_usingJsonPRenderer = false;

		$config = Api_Registry::getConfig();
		$request = $this->getRequest();

		/* If the oauth_token is old redirect to the connect page. */
		if ($request->getParam('oauth_verifier') && $_SESSION['oauth_token'] !== $request->getParam('oauth_token')) {
			$this->_redirect("/starbar/content/twitter-connect-redirect?user_id=".$this->user_id."&user_key=".$this->user_key."&auth_key=".$this->auth_key);
		}

		try {
			/* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
			$connection = new TwitterOAuth($config->twitter->consumer_key, $config->twitter->consumer_secret, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

			/* Request access tokens from twitter */
			$accessToken = $connection->getAccessToken($request->getParam('oauth_verifier'));

			if ($this->user_key && (int) $this->user_id === (int) Api_UserSession::getInstance($this->user_key)->getId()) {
				$userSocial = new User_Social();
				$userSocial->user_id = $this->user_id;
				$userSocial->provider = "twitter";
				$userSocial->identifier = $accessToken['user_id'];
				$userSocial->username = $accessToken['screen_name'];
				$userSocial->save();

				Game_Starbar::getInstance()->associateSocialNetwork($userSocial);

				// Show user congrats notification
				$message = new Notification_Message();
				$message->loadDataByUniqueFields(array('short_name' => 'TW Account Connected'));

				if ($message->id) {
					$messageUserMap = new Notification_MessageUserMap();
					$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($message->id, $this->user_id, false);
				}
			}

			$this->_redirect('/starbar/content/close-window?user_id='.$this->user_id.'&user_key='.$this->user_key.'&auth_key='.$this->auth_key);
		} catch (Exception $e) {}

		$this->_redirect('/starbar/content/twitter-fail?user_id='.$this->user_id.'&user_key='.$this->user_key.'&auth_key='.$this->auth_key);
	}

	public function onboardAction ()
	{

	}

	public function closeWindowAction ()
	{
		// this page is fetched in a popup, not via ajax
		$this->_usingJsonPRenderer = false;
	}

	public function facebookPostResultAction ()
	{
		// @todo re-enable this validation as necessary and remove the if() condition below
		//$this->_validateRequiredParameters(array('post_id', 'shared_type'));
		// ^^ Nay! Should show friendly message if user post doesn't work or if user decides not to post (this already works)

		// this page is fetched in an iframe, not ajax
		$this->_usingJsonPRenderer = false;

		$request = $this->getRequest();

		/* Facebook wall post successful */
		if ($request->getParam('post_id')) {
			Game_Starbar::getInstance()->share($this->shared_type, @$this->shared_id);

			// Send hidden game update notification to make the user request an update
			$message = new Notification_Message();
			$message->loadDataByUniqueFields(array('short_name' => 'Update Game'));

			if ($message->id) {
				$messageUserMap = new Notification_MessageUserMap();
				$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($message->id, $this->user_id, false);
			}
		}
		$this->_redirect('/starbar/content/close-window?user_id='.$this->user_id.'&user_key='.$this->user_key.'&auth_key='.$this->auth_key);
	}

	protected function _getBundleOfJoy ($surveyId, $nextSurveyId = -1)
	{
		$bundleOfJoy = "";
		$sep = "^|^"; // seperator between variables
		$eq = "^-^"; // seperator between variable name and value
		// e.g. user_id^-^1^|^user_key^-^123

		$request = $this->getRequest();

		// Include easyXDM variables so they can be used at the end of the survey for iframe communication
		// Namely, used by survey-complete and survey-disqualify
		$xdm_c = $request->getParam('xdm_c');
		$xdm_e = $request->getParam('xdm_e');
		$xdm_p = $request->getParam('xdm_p');

		$bundleData = array(
			'user_id' => $this->user_id,
			"user_key" => $this->user_key,
			"auth_key" => $this->auth_key,
			"survey_id" => $surveyId,
			"next_survey_id" => $nextSurveyId,
			"xdm_c" => $xdm_c,
			"xdm_e" => $xdm_e,
			"xdm_p" => $xdm_p,
		);

		foreach ($bundleData AS $name => $value) {
			if ($bundleOfJoy) $bundleOfJoy .= $sep;
			$bundleOfJoy .= $name . $eq . $value;
		}

		return $bundleOfJoy;
	}

	// Used so that the easyXDM variables (xdm_c, xdm_e, xdm_p)
	// are in the URL and can be used by easyXDM for our cross domain iframe communication
	protected function _replaceBundleOfJoyWithGetVariables()
	{
		$queryString = "";
		$request = $this->getRequest();

		if ($request->getParam('bundle_of_joy')) {
			foreach (explode('^|^', $request->getParam('bundle_of_joy')) as $keyPair) {
				if ($queryString) $queryString .= "&";
				else $queryString = "?";

				$parts = explode('^-^', $keyPair);
				$queryString .= $parts[0] . "=" . $parts[1];
			}
			$currentPage = $_SERVER['REQUEST_URI'];
			if (strpos($currentPage, "?") !== false) $currentPage = reset(explode("?", $currentPage));

			$this->_redirect(($_SERVER['HTTPS'] ? 'https' : 'http') . '://' . BASE_DOMAIN . $currentPage . $queryString);
		}
	}

	protected function _assignShareInfoToView($shareLink = null, $twitterShareText = null, $facebookShareCaption = null, $facebookCallbackUrl = null, $facebookTitle = null, $facebookDescription = null)
	{
		$config = Api_Registry::getConfig();

		$this->view->assign('facebook_app_id', $config->facebook->app_id);
		$this->view->assign('twitter_share_via_user', $config->twitter->share_via_user);
		$this->view->assign('twitter_share_related_users', $config->twitter->share_related_users);
		$this->view->assign('twitter_share_hashtags', $config->twitter->share_hashtags);

		$this->view->assign('twitter_share_text', $twitterShareText);

		$this->view->assign('share_link', $shareLink);

		$this->view->assign('facebook_share_caption', $facebookShareCaption);
		$this->view->assign('facebook_share_callback_url', $facebookCallbackUrl);
		$this->view->assign('facebook_title', $facebookTitle);
		$this->view->assign('facebook_description', $facebookDescription);
	}

	protected function _assignStarbarToView()
	{
		if (Registry::isRegistered('starbar')) {
			$starbar = Registry::getStarbar();
		} else {
			$starbar = new Starbar();
			$starbar->loadDataByUniqueFields(array('auth_key' => $this->auth_key));
		}

		$this->view->assign('starbar', $starbar);
	}

	// Sets view variables to display polls and surveys:
	// new_polls, completed_surveys, etc.
	// count_new_polls, count_completed_surveys, etc.
	protected function _assignSurveysToView($type)
	{
		$type = str_replace("surveys", "survey", $type);
		$type = str_replace("polls", "poll", $type);
		$statusArray = Array('completed', 'disqualified', 'archived', 'new', );
		$totalDisplayed = 0;

		if ($type == "poll" || $type == "survey") {
			$maximumDisplayed = $this->_maximumDisplayed[$type.'s'];
			foreach ($statusArray as $status) {
				$surveyCollection = new SurveyCollection();
				$surveyCollection->loadSurveysForStarbarAndUser($this->starbar_id, $this->user_id, $type, $status);
				$numberOfSurveys = sizeof($surveyCollection);

				if ($maximumDisplayed && $totalDisplayed >= $maximumDisplayed && $status == 'archived') {
					$this->view->assign('count_archived_'.$type.'s', 0);
					$this->view->assign('count_new_'.$type.'s', 0);
					break;
				} elseif ($maximumDisplayed && $totalDisplayed >= $maximumDisplayed && $status == 'new') {
					$this->view->assign('count_new_'.$type.'s', 0);
					break;
				}

				$this->view->assign($status.'_'.$type.'s', $surveyCollection);

				if ($maximumDisplayed && $numberOfSurveys > ($maximumDisplayed - $totalDisplayed) && ($status == 'archived' || $status == 'new')) {
					$numberOfSurveys = ($maximumDisplayed - $totalDisplayed);
				}

				$this->view->assign('count_'.$status.'_'.$type.'s', $numberOfSurveys);

				$totalDisplayed += $numberOfSurveys;
			}
		}
	}
}
