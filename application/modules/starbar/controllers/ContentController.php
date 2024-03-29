<?php

require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Starbar_ContentController extends Api_GlobalController
{
	// Render with JsonP by default
	protected $_usingJsonPRenderer = true;

	// To be set by inherited classes, e.g. HellomusicController
	protected $_maximumDisplayed = array('polls' => 0, 'surveys' => 0);

	public $starbar_content;

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
		parent::postDispatch();

		$request = $this->getRequest();

		$userActionLog = new User_ActionLog();
		$userActionLog->user_id = (int) $this->user_id;
		$userActionLog->starbar_id = (int) $this->starbar_id;
		$userActionLog->survey_id = (int) $request->getParam('survey_id');
		$userActionLog->good_id = (int) $request->getParam('good_id');
		$userActionLog->action = $request->getActionName();
		$userActionLog->save();

		$this->_assignStarbarToView();

		if( $this->frame_id )
			$this->view->assign('frame_id', $this->frame_id);

		if ($this->_usingJsonPRenderer) {
			$this->_enableRenderer(new Api_Plugin_JsonRenderer());
			// render from content directory phtml file
			$this->render('content/' . $request->getActionName(), null, true);
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
			$this->view->headLink()->appendStylesheet('/css/starbar-' . $this->view->starbar->short_name . '.css');

			// render from content directory phtml file
			$this->_helper->viewRenderer->setNoController();
			$this->_helper->viewRenderer->setScriptAction('content/' . $request->getActionName());
		}
	}

	public function starbarListAction() {
		$this->view->assign('starbars', User_State::getStarbarList($this->user_id, $this->starbar_id));
	}

	public function rewardsAction ()
	{
		$this->_validateRequiredParameters(array('user_id', 'starbar_id'));
		$this->view->chosen_good_id = $this->chosen_good_id;

		$this->view->rewards = Game_Transaction::getPurchasablesForUser($this->user_id, $this->starbar_id);
	}

	/**
	 * Get reward redemption view
	 *
	 * @throws Api_Exception
	 */
	public function rewardRedeemAction () {
		$this->_validateRequiredParameters(array('good_id', 'user_id', 'starbar_id'));
		$good = Game_Transaction::getPurchasableForUser($this->user_id, $this->starbar_id, $this->good_id);
		$economy_id = Economy::getIdforStarbar($this->starbar_id);
		$economy = Economy::getForId($economy_id);
		$balance = Game_Transaction::getBalance($this->user_id, $economy->getCurrencyIdByTypeId(Economy::CURRENCY_REDEEMABLE));

		if (!$good->can_purchase) {
			throw new Api_Exception(Api_Error::create(Api_Error::GAMING_ERROR, 'User ' . $this->user_id . ' illegally attempted to purchase good ' . $this->good_id));
		}

		$user = new User();
		$user->loadData($this->user_id);

		$userAddress = $user->getPrimaryAddress();

		$this->view->assign(array('good' => $good, 'user_balance' => $balance, 'user' => $user, 'user_address' => $userAddress));
		return $this->_resultType($good);
	}

	public function rewardRedeemedAction () {
		$this->_validateRequiredParameters(array('quantity', 'good_id', 'user_id', 'starbar_id'));
		$economy_id = Economy::getIdforStarbar($this->starbar_id);
		$economy = Economy::getForId($economy_id);
		$transaction_id = Game_Transaction::run( $this->user_id, $economy_id, 'PURCHASE', array('game_asset_id'=>$this->good_id, 'quantity'=>$this->quantity, 'starbar_id'=>$this->starbar_id));
		Game_Transaction::addGameToRequest($this->getRequest());
		$purchasable = new Item($economy->_purchasables[$this->good_id]);
		$goodTitle = $purchasable->name;

		$user = new User();
		$user->loadData($this->user_id);

		$userEmail = new User_Email();
		$userEmail->loadData($user->primary_email_id);

		$logRecord = new GamerOrderHistory();
		$logRecord->user_id = $this->user_id;
		$logRecord->first_name = $this->order_first_name;
		$logRecord->last_name = $this->order_last_name;
		$logRecord->street1 = $this->order_address_1;
		$logRecord->street2 = $this->order_address_2;
		$logRecord->locality = $this->order_city;
		$logRecord->region = $this->order_state;
		$logRecord->postalCode = $this->order_zip;
		$logRecord->country = $this->order_country;
		$logRecord->phone = $this->order_phone;
		$logRecord->game_asset_id = $this->good_id;
		$logRecord->quantity = $this->quantity;
		$logRecord->game_transaction_id = $transaction_id;
		$logRecord->save();

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

			$starbar = new Starbar();
			$starbar->loadData($this->starbar_id);

			/* Send a confirmation email to the admins */
			try {
				$message = '
					Redemption made for ' . $goodTitle . '

					Order Details
					=============
					Starbar: ' . $starbar->label . '
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
					Say.So Mailer v4.729
				';

				$config = Api_Registry::getConfig();
				$mail = new Mailer();
				$mail->setFrom('rewards@say.so')
					 ->addTo('rewards@say.so')
					 ->setSubject('['.strtoupper($starbar->short_name).'] Redemption of '.$goodTitle.' for '.$userEmail->email);
				$mail->setBodyMultilineText($message);
				$mail->send(new Zend_Mail_Transport_Smtp());
			} catch (Exception $e) {
				quickLog($message);
			}

						/* Send a confirmation email to the user */
			try {
				$address = $this->order_address_1;
				if (strlen($this->order_address_2) > 0) {
					$address .= "<br />".$this->order_address_2;
				}
				if( $this->starbar_id == 4 )
					$htmlmessage = "<h1>Machinima | Recon redemption made for ".$goodTitle."</h1>";
				else
					$htmlmessage = "<h1>Say.So redemption made for ".$goodTitle."</h1>";
				$htmlmessage .= sprintf("<p>Nicely done! You have successfully redeemed the item \"%s\" from the Reward Center!<br />We're kinda jealous...</p>",$goodTitle);
				$htmlmessage .= "<p>Right now, we are diligently processing your redemption so that you can receive your gift as soon as possible.</p>";
				$htmlmessage .= "<p>Delivery times vary by item type. Please allow 4-6 weeks for delivery of physical items; 5-7 days for virtual codes and items.</p>";
				if( $this->starbar_id == 4 )
					$htmlmessage .= "<p>Thank you for being a Machinima | Recon community member and we hope you enjoy your gift!<br />- The Machinima | Recon Team</p>";
				else
					$htmlmessage .= "<p>Thank you for being a Say.So community member and we hope you enjoy your gift!<br />- The Say.So Team</p>";

				$message = 'Nicely done! You have successfully redeemed the item "' . $goodTitle . '" from the Reward Center!
					We\'re kinda jealous...

					Right now, we are diligently processing your redemption so that you can receive your gift as soon as possible.

					Delivery times vary by item type. Please allow 4-6 weeks for delivery of physical items; 5-7 days for virtual codes and items.

				';
				if( $this->starbar_id == 4 )
				$message .= 'Thank you for being a Machinima | Recon community member and we hope you enjoy your gift!
					- The Machinima | Recon Team
				';
				else
				$message .= 'Thank you for being a Say.So community member and we hope you enjoy your gift!
					- The Say.So Team
				';

				$config = Api_Registry::getConfig();
				$mail = new Mailer();
				$mail->setFrom('rewards@say.so')
					 ->addTo($userEmail->email)
					 ->setSubject($this->starbar_id == 4 ? 'Your Machinima | Recon Item Redemption' : 'Your Item Redemption');
				$mail->setBodyMultilineText($message);
				$mail->setBodyHtml($htmlmessage);
				$mail->send(new Zend_Mail_Transport_Smtp());
			} catch (Exception $e) {
				quickLog($htmlmessage);
			}

		} else {

		}
		$this->view->assign(array('good' => $purchasable, 'user' => $user, 'user_address' => $userAddress));
		return $this->_resultType($purchasable);
	}

	public function aboutSaysoAction ()
	{
		$this->_validateRequiredParameters(array('starbar_id'));
		$profileSurvey = new Survey();
		$profileSurvey->loadProfileSurveyForStarbar($this->starbar_id);
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
		}

	}

	public function surveyUnavailableAction ()
	{
		// this page is fetched via an iframe, not ajax;
		$this->_usingJsonPRenderer = false;
	}

	public function surveyDisqualifyAction ()
	{
		$this->_validateRequiredParameters(array('srid'));
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

		// Find the next survey after this survey for this user before marking the survey disqualified
		$nextSurvey = Survey::getNextSurveyForUser($survey, $this->user_id);

		$surveyResponse->status = "disqualified";
		$surveyResponse->processing_status = "pending";
		$surveyResponse->completed_disqualified = new Zend_Db_Expr('now()');
		$surveyResponse->save();
		Game_Transaction::disqualifySurvey($this->user_id, $this->starbar_id, $survey);

		$user = new User();
		$user->loadData($this->user_id);

		// Set to http://www.samplicio.us/router2/ClientCallBack.aspx?fedResponseStatus=20&fedResponseID=xxxxx
		// for federated users who are disqualified on a federated survey (note fedResponseStatus = 20)
		if ($user->federated_id && $survey->is_federated) {
			$this->view->assign('pixel_iframe_url', "http://www.samplicio.us/router2/ClientCallBack.aspx?fedResponseStatus=20&fedResponseID=".$user->federated_id);
		} else {
			$this->view->assign('pixel_iframe_url', "");
		}

		$this->view->assign('survey', $survey);
		$this->view->assign('next_survey', $nextSurvey);

		$facebookCallbackUrl = "https://".BASE_DOMAIN."/starbar/content/facebook-post-result?shared_type=survey&shared_id=".$survey->id."&user_id=".$this->user_id."&user_key=".$this->user_key."&starbar_id=".$this->starbar_id;
		$this->_assignShareSurveyToView($survey, false, $facebookCallbackUrl);
	}

	public function surveyCompleteAction ()
	{
		$this->_validateRequiredParameters(array('srid'));
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

		// Find the next survey after this survey for this user before marking the survey completed
		$nextSurvey = Survey::getNextSurveyForUser($survey, $this->user_id);

		$surveyResponse->status = "completed";
		if ($survey->origin == "SurveyGizmo") {
			$surveyResponse->processing_status = "pending";
		} else {
			$surveyResponse->processing_status = "not required";
		}
		$surveyResponse->completed_disqualified = new Zend_Db_Expr('now()');
		$surveyResponse->save();
		Game_Transaction::completeSurvey($this->user_id, $this->starbar_id, $survey);

		$user = new User();
		$user->loadData($this->user_id);

		// Set to http://www.samplicio.us/router2/ClientCallBack.aspx?fedResponseStatus=10&fedResponseID=xxxxx
		// for federated users who have completed a federated survey (note fedResponseStatus = 10)
		if ($user->federated_id && $survey->is_federated) {
			$this->view->assign('pixel_iframe_url', "http://www.samplicio.us/router2/ClientCallBack.aspx?fedResponseStatus=10&fedResponseID=".$user->federated_id);
		} else {
			$this->view->assign('pixel_iframe_url', "");
		}

		$this->view->assign('survey', $survey);
		$this->view->assign('next_survey', $nextSurvey);

		$facebookCallbackUrl = "https://".BASE_DOMAIN."/starbar/content/facebook-post-result?shared_type=survey&shared_id=".$survey->id."&user_id=".$this->user_id."&user_key=".$this->user_key."&starbar_id=".$this->starbar_id;
		$this->_assignShareSurveyToView($survey, true, $facebookCallbackUrl);
	}

	public function surveyRedirectAction ()
	{
		$this->_validateRequiredParameters(array('survey_id', 'user_id', 'frame_id'));

		$survey = new Survey();
		$survey->loadData($this->survey_id);

		if (!$survey->id) exit;

		$this->_assignStarbarToView();

		$surveyResponse = new Survey_Response();
		$surveyResponse->loadDataByUniqueFields(array("user_id" => $this->user_id, "survey_id" => $this->survey_id));
		if (!$surveyResponse->id || $surveyResponse->status == "completed" || $surveyResponse->status == "disqualified") exit;

		$protocol = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) ? "https" : "http");

		switch ($survey->origin) {
			case "SurveyGizmo":
				$redirectUrl = $protocol . "://www.surveygizmo.com/s3/" . $survey->external_id . "/" . $survey->external_key;
				$redirectUrl .= "?starbar_short_name=" . $this->view->starbar->short_name;
				$redirectUrl .= "&srid=" . $surveyResponse->id;
				$redirectUrl .= "&size=" . $survey->size;
				$redirectUrl .= "&frame_id=" . $this->frame_id;
				if (APPLICATION_ENV == "production") {
					$redirectUrl .= "&testing=false";
				} else {
					$redirectUrl .= "&base_domain=" . BASE_DOMAIN;
					$redirectUrl .= "&testing=true";
				}
				break;

			case "UGAM":
				$redirectUrl = $protocol . "://" . $survey->external_key;
				$redirectUrl .= (strpos($redirectUrl, "?") === false ? "?" : "&");
				$redirectUrl .= "starbar_id=" . $this->view->starbar->id;
				$redirectUrl .= "&user_id=" . $this->user_id;
				$redirectUrl .= "&user_key=" . $this->user_key;
				$redirectUrl .= "&starbar_short_name=" . $this->view->starbar->short_name;
				$redirectUrl .= "&srid=" . $surveyResponse->id;
				$redirectUrl .= "&frame_id=" . $this->frame_id;
				$redirectUrl .= "&base_domain=" . BASE_DOMAIN;
				if (APPLICATION_ENV == "production") {
					$redirectUrl .= "&testing=false";
				} else {
					$redirectUrl .= "&testing=true";
				}
				break;

			default:
				exit;
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
		$profileSurvey->loadProfileSurveyForStarbar($this->starbar_id);
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
		$sql = "SELECT s.title, i.short_name
		          FROM survey s, survey_response sr, starbar_survey_map m, survey_mission_info i
		         WHERE s.id = sr.survey_id
		           AND s.id = m.survey_id
		           AND s.id = i.survey_id
		           AND m.starbar_id = ?
		           AND sr.status = 'completed'
		           AND sr.user_id = ?
		           AND s.type = 'mission'";
		$badges = Db_Pdo::fetchAll($sql, $this->starbar_id, $this->user_id);
		if($badges)
			$this->view->assign('badges', $badges);

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

			Game_Transaction::associateSocialNetwork( $this->user_id, $this->starbar_id, $userSocial );

			// Show user congrats notification
			$message = new Notification_Message();
			$message->loadByShortNameAndStarbarId('FB Account Connected', $this->starbar_id);

			if ($message->id) {
				$messageUserMap = new Notification_MessageUserMap();
				$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($message->id, $this->user_id, false);
			}
			$this->_redirect("/starbar/content/close-window?user_id=".$this->user_id."&user_key=".$this->user_key."&starbar_id=".$this->starbar_id."&update_notifications=true");
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

			$callbackUrl = "https://".BASE_DOMAIN."/starbar/content/twitter-connect-result?user_id=".$this->user_id."&user_key=".$this->user_key."&starbar_id=".$this->starbar_id;

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
			$this->_redirect("/starbar/content/twitter-fail?user_id=".$this->user_id."&user_key=".$this->user_key."&starbar_id=".$this->starbar_id);
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

					Game_Transaction::associateSocialNetwork( $this->user_id, $this->starbar_id, $userSocial );

					// Show user congrats notification
					$message = new Notification_Message();
					$message->loadByShortNameAndStarbarId('TW Account Connected', $this->starbar_id);

					if ($message->id) {
						$messageUserMap = new Notification_MessageUserMap();
						$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($message->id, $this->user_id, false);
					}
				}

				$this->_redirect("/starbar/content/close-window?user_id=".$this->user_id."&user_key=".$this->user_key."&starbar_id=".$this->starbar_id."&update_notifications=true");
				return;
			} catch (Exception $e) {}

			$this->_redirect("/starbar/content/twitter-fail?user_id=".$this->user_id."&user_key=".$this->user_key."&starbar_id=".$this->starbar_id);
		} else
			$this->_redirect("/starbar/content/twitter-connect-redirect?user_id=".$this->user_id."&user_key=".$this->user_key."&starbar_id=".$this->starbar_id);
	}

	public function onboardAction ()
	{
		$this->_validateRequiredParameters(array('starbar_id'));
		$profileSurvey = new Survey();
		$profileSurvey->loadProfileSurveyForStarbar($this->starbar_id);
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
			Game_Transaction::share($this->user_id, $this->starbar_id, $this->shared_type, "FB", @$this->shared_id);

			// Send hidden notification to make the user request an update to game info, and to disable sharing that same item on FB again
			$message = new Notification_Message();
			$message->loadByShortNameAndStarbarId('Update Game', $this->starbar_id);

			if ($message->id) {
				$messageUserMap = new Notification_MessageUserMap();
				$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($message->id, $this->user_id, false);
			}
		}
		$this->_redirect("/starbar/content/close-window?user_id=".$this->user_id."&user_key=".$this->user_key."&starbar_id=".$this->starbar_id."&update_notifications=true");
	}

    public function missionAction() {

		Survey_ResponseCollection::markUnseenSurveysNewForStarbarAndUser($this->starbar_id, $this->user_id, 'mission', 0);
		$surveyCollection = new SurveyCollection();
		$surveyCollection->loadSurveysForStarbarAndUser($this->starbar_id, $this->user_id, 'mission', 'new');

		foreach ($surveyCollection as $mission) {
			$missionInfo = new Survey_MissionInfo();
			$missionInfo->loadDataBySurveyId($mission->id);
			$missionInfo->title = $mission->title;
			$missionInfoCollection[] = $missionInfo;
		}

		// Also count the trailers for this user, and make that value available to the view
		Survey_ResponseCollection::markUnseenSurveysNewForStarbarAndUser($this->starbar_id, $this->user_id, 'trailer', 0);
		$trailerCollection = new SurveyCollection();
		$trailerCollection->loadSurveysForStarbarAndUser($this->starbar_id, $this->user_id, 'trailer', 'new');

		if($surveyCollection->count()) {
			$this->view->assign('missions_exist', TRUE);
			$this->view->assign('missioncount',count($surveyCollection));
			$this->view->assign('trailercount',count($trailerCollection));
			$this->view->assign('mission_info', $missionInfoCollection);
		}

    }

	public function spotlightAction() {

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

		// Get a count of available missions
		// Also count the trailers for this user, and make that value available to the view
		Survey_ResponseCollection::markUnseenSurveysNewForStarbarAndUser($this->starbar_id, $this->user_id, 'mission', 0);
		$missionCollection = new SurveyCollection();
		$missionCollection->loadSurveysForStarbarAndUser($this->starbar_id, $this->user_id, 'mission', 'new');

		$this->view->assign('missioncount',count($missionCollection));
		$this->view->assign('trailercount',count($trailers));


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

	protected function _assignShareAppToView($facebookCallbackUrl){}
	protected function _assignShareSurveyToView(Survey $survey, $completed, $facebookCallbackUrl){}
	protected function _assignSharePollToView(Survey $survey, $facebookCallbackUrl){}
	protected function _assignShareTrailerToView(Survey $survey, $facebookCallbackUrl){}

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
		$this->view->assign('starbar_id', $starbar->id);
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

		$surveyCollection = new SurveyCollection();
		$surveyCollection->loadSurveysForStarbarAndUser($this->starbar_id, $this->user_id, $type);

		$statusArray = Array('completed', 'disqualified', 'archived', 'new');
		$totalDisplayed = 0;

		$maximumDisplayed = $this->_maximumDisplayed[$typePlural];

		foreach ($statusArray as $status) {
			$this->view->assign('count_'.$status.'_'.$typePlural, 0);
			$this->view->assign($status.'_'.$typePlural, new SurveyCollection());
		}

		foreach ($surveyCollection as $survey) {
			$status = $survey->user_status;
			$countVariable = 'count_'.$status.'_'.$typePlural;
			$collectionVariable = $status.'_'.$typePlural;

			if (
				($maximumDisplayed && $totalDisplayed >= $maximumDisplayed && ($status == 'archived' || $status == 'new'))
				|| $this->view->$countVariable >= 50
				) {
				continue;
			}

			$this->view->$countVariable += 1;
			$this->view->$collectionVariable->addItem($survey);

			$totalDisplayed += 1;
		}
	}
}
