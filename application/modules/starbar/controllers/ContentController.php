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
			$this->_validateRequiredParameters(array('user_id', 'user_key', 'starbar_id'));
		} catch (Exception $e) {
			if ($e->getCode() == Api_Error::MISSING_PARAMETERS) {
				echo "This page can only be loaded via the app.";
			}
			exit;
		}
	}

	public function postDispatch()
	{
		$this->_assignStarbarToView();
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
			$this->view->headScript()->appendFile('/js/starbar/jquery.cycle.all.js');
			$this->view->headLink()->appendStylesheet('/css/starbar-generic.css');
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

		$user = new User();
		$user->loadData($this->user_id);

		$userAddress = $user->getPrimaryAddress();

		$this->view->assign(array('good' => $good, 'game' => Game_Starbar::getInstance(), 'user' => $user, 'user_address' => $userAddress));
		return $this->_resultType($good);
	}

		/**
		 * Redeem a 'Good'
		 * Redeems a 'Good' via BigDoor's API (basically removes the value of
		 * the good from the users credit), and sends confirmation emails to
		 * the client admins and the redeeming user.
		 *
		 * @return object - the Good being redeemed
		 */
	public function rewardRedeemedAction () {

		$this->_validateRequiredParameters(array('quantity', 'good_id', 'user_key'));

		$good = Api_Adapter::getInstance()->call('Gaming', 'getGoodFromStore');
		/* @var $good Gaming_BigDoor_Good */

		$game = Game_Starbar::getInstance();
		$game->purchaseGood($good, $this->quantity);

		/* Strip purchase words from the beginning of the $good->title */
		$searchArray = array("Purchase ", "Buy ", "Redeem ");
		$replaceArray   = array("", "", "");
		$goodTitle = str_ireplace($searchArray, $replaceArray, $good->title);

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
			$userAddress->phone = $this->order_phone;
			$userAddress->save();

			if (!$user->primary_address_id) {
				$user->primary_address_id = $userAddress->id;
			}

			$user->first_name = $this->order_first_name;
			$user->last_name = $this->order_last_name;
			$user->save();

			/* Send a confirmation email to the admins */
			try {
				$userEmail = new User_Email();
				$userEmail->loadData($user->primary_email_id);
				$message = '
					Snakkle Say.So redemption made for ' . $goodTitle . '

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
					Phone: ' . $this->order_phone . '
					User ID: ' . $this->user_id . '
					User Email: ' . $userEmail->email . '
					=============
					Thank you,
					Say.So Mailer v4.4
				';

				$config = Api_Registry::getConfig();
				$mail = new Mailer();
				$mail->setFrom('snakkleorders@say.so')
					 ->addTo('snakkleorders@say.so')
					 ->setSubject('Redemption of '.$goodTitle.' for '.$userEmail->email);
				$mail->setBodyMultilineText($message);
				$mail->send(new Zend_Mail_Transport_Smtp());
			} catch (Exception $e) {
				quickLog($message);
			}

						/* Send a confirmation email to the user */
						try {
				$userEmail = new User_Email();
				$address = $this->order_address_1;
				if (strlen($this->order_address_2) > 0) {
					$address .= "<br />".$this->order_address_2;
				}
				$userEmail->loadData($user->primary_email_id);
				$htmlmessage = "<h1>Snakkle Say.So redemption made for ".$goodTitle."</h1>";
				$htmlmessage .= sprintf("<p>This is your confirmation for the redemption of the item - %s.</p>",$goodTitle);
				$htmlmessage .= "<p>Congratulations! Your redemption is being processed.</p>";
				$htmlmessage .= "<p>Thank you for being a member of Snakkle Say.So!</p>";
				$htmlmessage .= "<p>- Snakkle Say.So Team</p>";

				$message = 'This is your confirmation for the redemption of the item - ' . $goodTitle . '

					Congratulations! Your redemption is being processed.

					Thank you for being a member of Snakkle Say.So!

					- Snakkle Say.So Team
				';

				$config = Api_Registry::getConfig();
				$mail = new Mailer();
				$mail->setFrom('snakkleorders@say.so')
					 ->addTo($userEmail->email)
					 ->setSubject('Your Item Redemption');
				$mail->setBodyMultilineText($message);
				$mail->setBodyHtml($htmlmessage);
				$mail->send(new Zend_Mail_Transport_Smtp());
			} catch (Exception $e) {
				quickLog($htmlmessage);
			}

		} else {

		}
		$this->view->assign(array('game' => $game, 'good' => $good, 'user' => $user, 'user_address' => $userAddress));
		return $this->_resultType($good);
	}

	public function aboutSaysoAction ()
	{
		$this->_validateRequiredParameters(array('starbar_id'));
		$profileSurvey = new Survey();
		$profileSurvey->loadDataByUniqueFields(array("starbar_id" => $this->starbar_id, "reward_category" => "profile"));
		if ($profileSurvey->id) $this->view->profile_survey_id = $profileSurvey->id;
		else $this->view->profile_survey_id = 0;
		$this->view->assign('show_testing_function', in_array(APPLICATION_ENV, array('development', 'sandbox', 'testing', 'demo')));
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
		$this->_assignStarbarToView();

		$this->view->user_id = $this->user_id;
		$this->view->user_key = $this->user_key;
		$this->view->starbar_id = $this->starbar_id;

		$facebookCallbackUrl = "https://".BASE_DOMAIN."/starbar/content/facebook-post-result?shared_type=poll&shared_id=".$survey->id."&user_id=".$this->user_id."&user_key=".$this->user_key."&starbar_id=".$this->starbar_id;
		$this->_assignSharePollToView($survey, $facebookCallbackUrl);
	}

	// Embed a single SG survey. Expects "survey_id" passed via URL (GET)
	public function embedSurveyAction ()
	{
		$this->_validateRequiredParameters(array('survey_id', 'user_id'));

		$survey = new Survey();
		$survey->loadData($this->survey_id);

		$this->view->assign('survey', $survey);

		$surveyResponse = new Survey_Response();
		$surveyResponse->loadDataByUniqueFields(array("user_id" => $this->user_id, "survey_id" => $this->survey_id));

		if (!$surveyResponse->id) {
			// Failed... might be because it's a new user. Try again after marking unseen surveys new
			Survey_ResponseCollection::markUnseenSurveysNewForStarbarAndUser($this->starbar_id, $this->user_id, 'surveys', $this->_maximumDisplayed['surveys']);

			$surveyResponse->loadDataByUniqueFields(array("user_id" => $this->user_id, "survey_id" => $this->survey_id));
		}

		if (!$surveyResponse->id) {
			throw new Api_Exception(Api_Error::create(Api_Error::APPLICATION_ERROR, 'User ' . $this->user_id . ' could not take survey ' . $this->survey_id . ' because there is no survey_response record'));
			exit;
		}

		$surveyAlreadyCompleted = ($surveyResponse->status == "completed" || $surveyResponse->status == "disqualified");

		if ($surveyAlreadyCompleted) {
			$this->view->assign('survey_already_completed', true);
		} else {
			$this->view->assign('survey_already_completed', false);

			// Find the next survey after this survey for this user
			$nextSurvey = Survey::getNextSurveyForUser($survey, $this->user_id);
			if ($nextSurvey->id) $nextSurveyId = $nextSurvey->id;
			else $nextSurveyId = -1;

			$this->view->assign('next_survey_id', $nextSurveyId);
		}

	}

	public function surveyUnavailableAction ()
	{
		// this page is fetched via an iframe, not ajax;
		$this->_usingJsonPRenderer = false;
	}

	public function surveyDisqualifyAction ()
	{
		$this->_validateRequiredParameters(array('srid', 'next_survey_id'));
		// this page is fetched via an iframe, not ajax;
		$this->_usingJsonPRenderer = false;

		$surveyResponse = new Survey_Response();
		$surveyResponse->loadData($this->srid);

		if ( !$surveyResponse->id
			|| $surveyResponse->user_id != $this->user_id
			|| $surveyResponse->status == "completed"
			|| $surveyResponse->status == "disqualified")
			exit;

		$survey = new Survey();
		$survey->loadData($surveyResponse->survey_id);

		$surveyResponse->status = "disqualified";
		$surveyResponse->processing_status = "pending";
		$surveyResponse->completed_disqualified = new Zend_Db_Expr('now()');
		$surveyResponse->save();
		Game_Starbar::getInstance()->disqualifySurvey($survey);

		$nextSurvey = new Survey();
		if ($this->next_survey_id != -1) {
			$nextSurvey->loadData($this->next_survey_id);
		}

		$this->view->assign('survey', $survey);
		$this->view->assign('next_survey', $nextSurvey);

		$facebookCallbackUrl = "https://".BASE_DOMAIN."/starbar/content/facebook-post-result?shared_type=survey&shared_id=".$survey->id."&user_id=".$this->user_id."&user_key=".$this->user_key."&starbar_id=".$this->starbar_id;
		$this->_assignShareSurveyToView($survey, false, $facebookCallbackUrl);
	}

	public function surveyCompleteAction ()
	{
		$this->_validateRequiredParameters(array('srid', 'next_survey_id'));
		// this page is fetched via an iframe, not ajax;
		$this->_usingJsonPRenderer = false;

		$surveyResponse = new Survey_Response();
		$surveyResponse->loadData($this->srid);

		if ( !$surveyResponse->id
			|| $surveyResponse->user_id != $this->user_id
			|| $surveyResponse->status == "completed"
			|| $surveyResponse->status == "disqualified")
			exit;

		$survey = new Survey();
		$survey->loadData($surveyResponse->survey_id);

		$surveyResponse->status = "completed";
		$surveyResponse->processing_status = "pending";
		$surveyResponse->completed_disqualified = new Zend_Db_Expr('now()');
		$surveyResponse->save();
		Game_Starbar::getInstance()->completeSurvey($survey);

		$nextSurvey = new Survey();
		if ($this->next_survey_id != -1) {
			$nextSurvey->loadData($this->next_survey_id);
		}

		$this->view->assign('survey', $survey);
		$this->view->assign('next_survey', $nextSurvey);

		$facebookCallbackUrl = "https://".BASE_DOMAIN."/starbar/content/facebook-post-result?shared_type=survey&shared_id=".$survey->id."&user_id=".$this->user_id."&user_key=".$this->user_key."&starbar_id=".$this->starbar_id;
		$this->_assignShareSurveyToView($survey, true, $facebookCallbackUrl);
	}

	public function surveyRedirectAction ()
	{
		$this->_validateRequiredParameters(array('survey_id', 'user_id', 'next_survey_id', 'xdm_c', 'xdm_e', 'xdm_p'));

		$survey = new Survey();
		$survey->loadData($this->survey_id);

		if (!$survey->id) exit;

		$this->_assignStarbarToView();

		$surveyResponse = new Survey_Response();
		$surveyResponse->loadDataByUniqueFields(array("user_id" => $this->user_id, "survey_id" => $this->survey_id));
		if (!$surveyResponse->id || $surveyResponse->status == "completed" || $surveyResponse->status == "disqualified") exit;

		$protocol = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) ? "https" : "http");

		$redirectUrl = $protocol . "://www.surveygizmo.com/s3/" . $survey->external_id . "/" . $survey->external_key;
		$redirectUrl .= "?next_survey_id=" . $this->next_survey_id;
		$redirectUrl .= "&starbar_short_name=" . $this->view->starbar->short_name;
		$redirectUrl .= "&srid=" . $surveyResponse->id;
		$redirectUrl .= "&size=" . $survey->size;
		$redirectUrl .= "&xdm_c=" . $this->xdm_c;
		$redirectUrl .= "&xdm_e=" . $this->xdm_e;
		$redirectUrl .= "&xdm_p=" . $this->xdm_p;
		if (APPLICATION_ENV == "production") {
			$redirectUrl .= "&testing=false";
		} else {
			$redirectUrl .= "&base_domain=" . BASE_DOMAIN;
			$redirectUrl .= "&testing=true";
		}

		$this->_redirect($redirectUrl);
	}

	// Fetches polls for the current user for display
	public function pollsAction ()
	{
		Survey_ResponseCollection::markOldSurveysArchivedForStarbarAndUser($this->starbar_id, $this->user_id, 'polls');
		Survey_ResponseCollection::markUnseenSurveysNewForStarbarAndUser($this->starbar_id, $this->user_id, 'polls', $this->_maximumDisplayed['polls']);
		$this->_assignSurveysToView('polls');
	}

	// Fetches surveys for the current user for display
	public function surveysAction ()
	{
		Survey_ResponseCollection::markOldSurveysArchivedForStarbarAndUser($this->starbar_id, $this->user_id, 'surveys');
		Survey_ResponseCollection::markUnseenSurveysNewForStarbarAndUser($this->starbar_id, $this->user_id, 'surveys', $this->_maximumDisplayed['surveys']);
		$this->_assignSurveysToView('surveys');
		$this->view->profile_survey_id = 0;
		if (isset($this->view->new_surveys) && sizeof($this->view->new_surveys)) {
			foreach ($this->view->new_surveys as $survey) {
				if ($survey->reward_category == "profile") {
					$this->view->profile_survey_id = $survey->id;
					break;
				}
			}
		}
	}

	public function promosAction ()
	{
		$this->_validateRequiredParameters(array('starbar_id'));
		$profileSurvey = new Survey();
		$profileSurvey->loadDataByUniqueFields(array("starbar_id" => $this->starbar_id, "reward_category" => "profile"));
		if ($profileSurvey->id) $this->view->profile_survey_id = $profileSurvey->id;
		else $this->view->profile_survey_id = 0;
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
		if($user->primary_email_id)
			$userEmail->loadData($user->primary_email_id);
		$this->view->assign('user_email', $userEmail);

		// Assign the counts for surveys and polls
		Survey_ResponseCollection::markUnseenSurveysNewForStarbarAndUser($this->starbar_id, $this->user_id, 'polls', $this->_maximumDisplayed['polls']);
		$this->_assignSurveysToView('polls');
		Survey_ResponseCollection::markUnseenSurveysNewForStarbarAndUser($this->starbar_id, $this->user_id, 'surveys', $this->_maximumDisplayed['surveys']);
		$this->_assignSurveysToView('surveys');

		$facebookCallbackUrl = "https://".BASE_DOMAIN."/starbar/content/facebook-post-result?shared_type=starbar&shared_id=".$this->starbar_id."&user_id=".$this->user_id."&user_key=".$this->user_key."&starbar_id=".$this->starbar_id;
		$this->_assignShareAppToView($facebookCallbackUrl);
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

		if ($fbUser && $this->user_id) {
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
			$message->loadByShortNameAndStarbarId('FB Account Connected', $this->starbar_id);

			if ($message->id) {
				$messageUserMap = new Notification_MessageUserMap();
				$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($message->id, $this->user_id, false);
			}
			$this->_redirect('/starbar/content/close-window?user_id='.$this->user_id.'&user_key='.$this->user_key."&starbar_id=".$this->starbar_id);
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

			$callbackUrl = 'https://'.BASE_DOMAIN.'/starbar/content/twitter-connect-result?user_id='.$this->user_id.'&user_key='.$this->user_key."&starbar_id=".$this->starbar_id;

			/* Get temporary credentials and set the callback URL. */
			$twitterRequestToken = $connection->getRequestToken($callbackUrl);

			/* Save temporary credentials to cache. */
			$oauth['token'] = $twitterRequestToken['oauth_token'];
			$oauth['token_secret'] = $twitterRequestToken['oauth_token_secret'];
			$cache = Api_Cache::getInstance('Twitter_OAuth_'.$this->user_key, Api_Cache::LIFETIME_HOUR);
			$cache->save($oauth);

			if ($twitterRequestToken['oauth_callback_confirmed'] == 'true') $success = true;
		} catch (Exception $e) {}

		if ($success) {
			$this->_redirect("https://api.twitter.com/oauth/authorize?oauth_token=".$twitterRequestToken['oauth_token']);
		} else {
			$this->_redirect('/starbar/content/twitter-fail?user_id='.$this->user_id.'&user_key='.$this->user_key."&starbar_id=".$this->starbar_id);
		}
	}

	public function twitterConnectResultAction ()
	{
		// this page is fetched in a popup, not ajax
		$this->_usingJsonPRenderer = false;

		$config = Api_Registry::getConfig();

		$cache = Api_Cache::getInstance('Twitter_OAuth_'.$this->user_key, Api_Cache::LIFETIME_HOUR);

		if( $cache->test() && ($oauth = $cache->load()) && $this->oauth_verifier && $this->oauth_token == $oauth['token']) {
			try {
				/* Create TwitterOAuth object with app key/secret and token key/secret from default phase */
				$connection = new TwitterOAuth($config->twitter->consumer_key, $config->twitter->consumer_secret, $oauth['token'], $oauth['token_secret']);

				/* Request access tokens from twitter */
				$accessToken = $connection->getAccessToken($this->oauth_verifier);

				if ($this->user_id) {
					$userSocial = new User_Social();
					$userSocial->user_id = $this->user_id;
					$userSocial->provider = "twitter";
					$userSocial->identifier = $accessToken['user_id'];
					$userSocial->username = $accessToken['screen_name'];
					$userSocial->save();

					Game_Starbar::getInstance()->associateSocialNetwork($userSocial);

					// Show user congrats notification
					$message = new Notification_Message();
					$message->loadByShortNameAndStarbarId('TW Account Connected', $this->starbar_id);

					if ($message->id) {
						$messageUserMap = new Notification_MessageUserMap();
						$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($message->id, $this->user_id, false);
					}
				}

				$this->_redirect('/starbar/content/close-window?user_id='.$this->user_id.'&user_key='.$this->user_key."&starbar_id=".$this->starbar_id);
				return;
			} catch (Exception $e) {}

			$this->_redirect('/starbar/content/twitter-fail?user_id='.$this->user_id.'&user_key='.$this->user_key."&starbar_id=".$this->starbar_id);
		} else
			$this->_redirect("/starbar/content/twitter-connect-redirect?user_id=".$this->user_id."&user_key=".$this->user_key."&starbar_id=".$this->starbar_id);
	}

	public function onboardAction ()
	{
		$this->_validateRequiredParameters(array('starbar_id'));
		$profileSurvey = new Survey();
		$profileSurvey->loadDataByUniqueFields(array("starbar_id" => $this->starbar_id, "reward_category" => "profile"));
		if ($profileSurvey->id) $this->view->profile_survey_id = $profileSurvey->id;
		else $this->view->profile_survey_id = 0;
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
			Game_Starbar::getInstance()->share($this->shared_type, "FB", @$this->shared_id);

			// Send hidden notification to make the user request an update to game info, and to disable sharing that same item on FB again
			$message = new Notification_Message();
			$message->loadByShortNameAndStarbarId('Facebook Post', $this->starbar_id);

			if ($message->id) {
				$messageUserMap = new Notification_MessageUserMap();
				$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($message->id, $this->user_id, false);
			}
		}
		$this->_redirect('/starbar/content/close-window?user_id='.$this->user_id.'&user_key='.$this->user_key."&starbar_id=".$this->starbar_id);
	}

	protected function _assignShareInfoToView($shareLink = null, $twitterShareText = null, $facebookShareCaption = null, $facebookCallbackUrl = null, $facebookTitle = null, $facebookDescription = null)
	{
		$config = Api_Registry::getConfig();

		$this->view->assign('facebook_app_id', $config->facebook->app_id);
		$this->view->assign('twitter_share_via_user', $config->twitter->share_via_user);
		$this->view->assign('twitter_share_related_users', $config->twitter->share_related_users);
		$this->view->assign('twitter_share_hashtags', $config->twitter->share_hashtags);

		$this->view->assign('twitter_share_text', htmlspecialchars($twitterShareText));

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
			if( $this->starbar_id )
				$starbar->loadData($this->starbar_id);
			else {
				$userstate = new User_State();
				$userstate->loadDataByUniqueFields(array('user_id'=>$this->user_id));

				$starbar->loadData($userstate->starbar_id);
			}
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
		$type = str_replace("quizzes", "quiz", $type);
		$type = str_replace("trailers", "trailer", $type);

		switch ($type) {
			case "survey":
				$typePlural = "surveys";
				break;
			case "poll":
				$typePlural = "polls";
				break;
			case "quiz":
				$typePlural = "quizzes";
				break;
			case "trailer":
				$typePlural = "trailers";
				break;
			default:
				return;
				break;
		}

		$statusArray = Array('completed', 'disqualified', 'archived', 'new', );
		$totalDisplayed = 0;

		$maximumDisplayed = $this->_maximumDisplayed[$typePlural];

		foreach ($statusArray as $status) {
			$surveyCollection = new SurveyCollection();
			$surveyCollection->loadSurveysForStarbarAndUser($this->starbar_id, $this->user_id, $type, $status);
			$numberOfSurveys = sizeof($surveyCollection);

			if ($maximumDisplayed && $totalDisplayed >= $maximumDisplayed && $status == 'archived') {
				$this->view->assign('count_archived_'.$typePlural, 0);
				$this->view->assign('count_new_'.$typePlural, 0);
				break;
			} elseif ($maximumDisplayed && $totalDisplayed >= $maximumDisplayed && $status == 'new') {
				$this->view->assign('count_new_'.$typePlural, 0);
				break;
			}

			$this->view->assign($status.'_'.$typePlural, $surveyCollection);

			if ($maximumDisplayed && $numberOfSurveys > ($maximumDisplayed - $totalDisplayed) && ($status == 'archived' || $status == 'new')) {
				$numberOfSurveys = ($maximumDisplayed - $totalDisplayed);
			}

			$this->view->assign('count_'.$status.'_'.$typePlural, $numberOfSurveys);

			$totalDisplayed += $numberOfSurveys;
		}
	}
}
