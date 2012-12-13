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

	/**
	 * Redeem a 'Good'
	 * Redeems a 'Good' via BigDoor's API (basically removes the value of
	 * the good from the users credit), and sends confirmation emails to
	 * the client admins and the redeeming user.
	 * Overrides ContentController version to provide Machinima specific text
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

		$userEmail = new User_Email();
		$userEmail->loadData($user->primary_email_id);

		$gamer = $game->getGamer(false);

		$logRecord = new GamerOrderHistory();
		if ($gamer->id) {
			$logRecord->user_gaming_id = $gamer->id;
			$logRecord->first_name = $this->order_first_name;
			$logRecord->last_name = $this->order_last_name;
			$logRecord->street1 = $this->order_address_1;
			$logRecord->street2 = $this->order_address_2;
			$logRecord->locality = $this->order_city;
			$logRecord->region = $this->order_state;
			$logRecord->postalCode = $this->order_zip;
			$logRecord->country = $this->order_country;
			$logRecord->phone = $this->order_phone;
			$logRecord->good_id = $good->id;
			$logRecord->quantity = $this->quantity;
			$logRecord->save();
		}

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

			$starbar = Registry::get('starbar');

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
				$htmlmessage = "<h1>Machinima | Recon redemption made for ".$goodTitle."</h1>";
				$htmlmessage .= sprintf("<p>Nicely done! You have successfully redeemed the item \"%s\" from the Reward Center!<br />We're kinda jealous...</p>",$goodTitle);
				$htmlmessage .= "<p>Right now, we are diligently processing your redemption so that you can receive your gift as soon as possible.</p>";
				$htmlmessage .= "<p>Delivery times vary by item type. Please allow 4-6 weeks for delivery of physical items; 5-7 days for virtual codes and items.</p>";
				$htmlmessage .= "<p>Thank you for being a Machinima | Recon community member and we hope you enjoy your gift!<br />- The Machinima | Recon Team</p>";

				$message = 'Nicely done! You have successfully redeemed the item "' . $goodTitle . '" from the Reward Center!
					We\'re kinda jealous...

					Right now, we are diligently processing your redemption so that you can receive your gift as soon as possible.

					Delivery times vary by item type. Please allow 4-6 weeks for delivery of physical items; 5-7 days for virtual codes and items.

					Thank you for being a Machinima | Recon community member and we hope you enjoy your gift!
					- The Machinima | Recon Team
				';

				$config = Api_Registry::getConfig();
				$mail = new Mailer();
				$mail->setFrom('rewards@say.so')
					 ->addTo($userEmail->email)
					 ->setSubject('Your Machinima | Recon Item Redemption');
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
			$this->view->current_trailer = $currentTrailer;

			$firstQuestion = new Survey_Question();
			$firstQuestion->loadDataByUniqueFields(array('survey_id' => $currentTrailer->id, 'ordinal' => 1));

			$this->view->first_question = $firstQuestion;

			$firstQuestionChoices = new Survey_QuestionChoiceCollection();
			$firstQuestionChoices->loadAllChoicesForSurveyQuestion($firstQuestion->id);

			$this->view->first_question_choices = $firstQuestionChoices;

			$secondQuestion = new Survey_Question();
			$secondQuestion->loadDataByUniqueFields(array('survey_id' => $currentTrailer->id, 'ordinal' => 2));

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

		$this->view->assign('facebook_share_image_url', 'http://app-dev.saysollc.com/images/social/FB_Share_Icon_100px.jpg');
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
		$sharepollTitle = "Machinima | Recon wants to know '".$pollTitle."'";
		$twShareText = "I've finished a Machinima | Recon poll and earned " . $redeemable ." Coins. Get the Machinima | Recon app @ ";
		$fbkShareText = "I just earned " . $redeemable ." Coins for answering the poll '".$pollTitle."'";

		$fbkAppDescription = $this->_fbkOffer . "\n" . $this->_fbkAppDescription;

		$this->_assignShareInfoToView($this->_appShareLink, $twShareText, $fbkShareText, $facebookCallbackUrl, $sharepollTitle, $fbkAppDescription);
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
		$TrailerTitle = substr_compare($survey->title, '?', -1, 1) === 0 ? substr($survey->title, 0, -1) : $survey->title;
		$sharepollTitle = $TrailerTitle;
		$twShareText = "I've rated Machinima | Recon content and earned " . $redeemable ." Coins. Get the Machinima | Recon app @ ";
		$fbkShareText = "I've rated Machinima | Recon content and earned " . $redeemable ." Coins.";

		$fbkAppDescription = $this->_fbkOffer . "\n" . $this->_fbkAppDescription;

		$this->_assignShareInfoToView($this->_appShareLink, $twShareText, $fbkShareText, $facebookCallbackUrl, $survey->title, $fbkAppDescription);
	}
}
