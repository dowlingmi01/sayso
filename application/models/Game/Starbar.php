<?php

/**
 * Game class for Starbars
 *
 * General info about Game classes:
 *
 * Abstracting the game via a separate "Game" class allows
 * fine-tuning the game, by (1) running multiple transactions per
 * action, (2) adding conditional logic based on the request object,
 * OR (3) whatever other logic you need to put in the method
 *
 * Method naming usually corresponds to the controller+action
 * where the transaction should be triggered. So for example,
 * Gaming controller with action testBigDoorAction could have
 * a method named gamingTestBigDoor. You can also name methods
 * arbitrarily, in cases where there is no direct correlation
 * between the controller/action and the transaction. The benefit
 * of using the naming convention however is that you can than
 * automate the game (e.g. via postDipatch) like so:
 * Game_Factory::create($gamer, $this->_request)->trigger()
 *
 * About this particular class:
 *
 * Aside from the transaction methods, this class also wraps:
 * - creation of the specific Game class, which is determined
 *   by the developer.application record for the current Starbar.
 *   see static create() below for more information
 * - submitting the action including trapping/reporting exceptions
 *   and building the user profile to return in the response
 * 	 see submitAction() below
 *
 * @author davidbjames
 *
 */
abstract class Game_Starbar extends Game_Abstract {

	const SHARE_POLL = 'poll';
	const SHARE_SURVEY = 'survey';
	const SHARE_QUIZ = 'quiz';
	const SHARE_TRAILER = 'trailer';
	const SHARE_STARBAR = 'starbar';
	const SHARE_PROMOS = 'promos';

	public static $userHasCompletedProfileSurvey = null;
	public static $profileSurveyId = null;

	public function init() {
		$this->loadLevels();
		parent::init();
	}

	public function gamingTestBigDoor () {
		$this->submitAction('POLL_STANDARD');
	}

	public function install () {
		$this->submitAction('STARBAR_OPT_IN');
	}

	public function checkin () {
		$this->submitAction('STARBAR_CHECKIN');
	}
//public function checkin () {
//		$this->submitAction('STARBAR_CHECKIN');
//	}
	/* was testRewardNotes*/
	public function rewardNotes () {
		if (in_array(APPLICATION_ENV, array('development', 'sandbox', 'testing', 'demo'))) {
			$this->submitAction('TEST_REWARD_NOTES');
		}
	}

	public function completeSurvey (Survey $survey) {
		if( $survey->custom_reward_experience && $survey->custom_reward_redeemable ) {
			$this->submitAction('ADHOC_EXPERIENCEPOINTS', $survey->custom_reward_experience, $survey->id);
			$this->submitAction('ADHOC_REDEEMABLEPOINTS', $survey->custom_reward_redeemable, $survey->id);
		} else if (in_array($survey->type, array('survey', 'poll', 'quiz', 'trailer')) && in_array($survey->reward_category, array('standard', 'premium', 'profile'))) {
			// POLL_STANDARD, SURVEY_PROFILE, QUIZ_PREMIUM, etc.
			$this->submitAction(strtoupper($survey->type.'_'.$survey->reward_category), 0, $survey->id);
		}
	}

	public function disqualifySurvey (Survey $survey) {
		// POLL_STANDARD_DISQUALIFIED, SURVEY_PROFILE_DISQUALIFIED, QUIZ_PREMIUM_DISQUALIFIED, etc.
		if (in_array($survey->type, array('survey', 'poll', 'quiz', 'trailer')) && in_array($survey->reward_category, array('standard', 'premium', 'profile'))) {
			$this->submitAction(strtoupper($survey->type.'_'.$survey->reward_category).'_DISQUALIFIED', 0, $survey->id);
		}
	}

	public function share ($type, $network, $typeId = 0) {
		$network = strtoupper($network);

		if (!in_array($network, array("FB", "TW"))) {
			throw new Api_Exception(Api_Error::create(Api_Error::GAMING_ERROR, 'Cannot award user. No social network specified.'));
		}

		$shareString = "";

		switch ($type) {
			case self::SHARE_POLL :
			case self::SHARE_SURVEY :
			case self::SHARE_QUIZ :
			case self::SHARE_TRAILER :
				if (!$typeId) {
					throw new Api_Exception(Api_Error::create(Api_Error::GAMING_ERROR, 'Cannot award user for sharing survey/poll/quiz/trailer. Survey id missing from call and required in order to determine if standard or premium.'));
				}
				$survey = new Survey();
				$survey->loadData($typeId);
				// e.g. FB_QUIZ_STANDARD_SHARE, TW_SURVEY_PREMIUM_SHARE, etc.
				$shareString = strtoupper($network.'_'.$survey->type.'_'.$survey->reward_category).'_SHARE';
				break;
			case self::SHARE_STARBAR :
				$shareString = $network.'_SHARE_STARBAR';
				break;
			case self::SHARE_PROMOS :
				$shareString = $network.'_SHARE_PROMOS';
				break;
			default :
				throw new Api_Exception(Api_Error::create(Api_Error::GAMING_ERROR, 'Wrong type (' . $type . ') supplied to Game_Starbar::share(). See Game_Starbar "SHARE" constants for allowed types.'));
		}

		if ($shareString && $this->canShare($shareString, $type, $typeId)) {
			$this->submitAction($shareString, 0, $typeId);
		}
	}

	public function canShare($shareString, $type, $typeId) {
		$gamer = $this->getGamer(false);
		$typeId = (int) $typeId;
		if ($gamer->id) {
			$sql = "
				SELECT id
				FROM user_gaming_transaction_history
				WHERE user_gaming_id = ?
					AND action = ?
			";
			if ($typeId) $sql .= " AND action_on_id = " . $typeId . " ";

			if ($type == self::SHARE_STARBAR) $sql .= " AND created > now() - INTERVAL 1 day ";

			$result = Db_Pdo::fetch($sql, $gamer->id, $shareString);

			if (!$result || !isset($result['id'])) {
				return true;
			}
		}
		throw new Api_Exception(Api_Error::create(Api_Error::GAMING_ERROR, 'Cannot award user_gaming_id '.$gamer->id.'. Already shared '.$shareString.'-'.$typeId.'.'));
		return false;
	}

	public function viewPromos () {
		$this->submitAction('PROMOS_VIEW');
	}

	public function completeProfile (User $user) {
		if ($user->username && $user->primary_email_id) {
			$this->submitAction('PROFILE_COMPLETE');
		}
	}

	public function associateSocialNetwork (User_Social $userSocial) {
		switch ($userSocial->provider) {
			case 'facebook' :
				$this->submitAction('FACEBOOK_ASSOCIATE');
				break;
			case 'twitter' :
				$this->submitAction('TWITTER_ASSOCIATE');
				break;
			default :
				throw new Api_Exception(Api_Error::create(Api_Error::GAMING_ERROR, 'Wrong or missing social user provider (' . $userSocial->provider . ') supplied to Game_Starbar::associateSocialNetwork().'));
				break;
		}
	}

	abstract public function getPurchaseCurrencyId ();

	/**
	 * Override parent::submitAction to grab user profile with updated
	 * points/currencies *after* the transaction has fired and load that
	 * onto the gaming user. Furthermore, attach this gaming user to
	 * the request via 'custom' parameter. IF the request is already returning
	 * a Gaming user, then this will be ignored.
	 *
	 * @see Game_Abstract::submitAction()
	 */
	public function submitAction ($actionId, $customAmount = 0, $sharedId = null) {

		$logRecord = new GamerTransactionHistory();

		try {

			if (!Game_Abstract::$_enabled) return false;

			parent::submitAction($actionId, $customAmount);

			$gamer = $this->getGamer(/* load profile */);

			if ($gamer->id) {
				$logRecord->user_gaming_id = $gamer->id;
				$logRecord->action = $actionId;

				// Get defined currency and points for this BD ID.
				// Note that these values come from the XML file, not from BD itself.

				$logRecord->experience_points = $this->_economy->getActionExperiencePoints($actionId);
				$logRecord->redeemable_points = $this->_economy->getActionRedeemablePoints($actionId);
				$logRecord->starbar_id = $this->_request->getParam('starbar_id');
				$logRecord->source = 'Say.So Starbar';

				if ($sharedId) $logRecord->action_on_id = $sharedId;
				$logRecord->save();
				if( $gamer->imported && !is_numeric($this->_client->getData())) {
					$parameters = array('gamer'=>$gamer);
					if( $sharedId )
						$parameters['survey_id'] = $sharedId;
                    if( $customAmount )
                        $parameters['custom_amount'] = $customAmount;
						
					Game_Transaction::run($gamer->user_id, $gamer->starbar_id, $actionId, $parameters);
				}
			}

			// if user just leveled up, congratulate via notification
			if ($gamer->justLeveledUp() && $gamer->getLevels()->count() > 1) {
				$messageGroup = new Notification_MessageGroup();
				$messageGroup->loadDataByUniqueFields(array('short_name' => 'User Actions', 'starbar_id' => $this->_request->getParam('starbar_id')));
				$message = new Notification_Message();

				if ($messageGroup->id)
					$message->loadDataByUniqueFields(array('short_name' => 'Level Up', 'notification_message_group_id' => $messageGroup->id));

				if ($message->id) {
					$messageUserMap = new Notification_MessageUserMap();
					$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($message->id, (int) $this->_request->getParam('user_id'));
				}

				if( $gamer->imported )
					Game_Transaction::run($gamer->user_id, $gamer->starbar_id, 'CHECK_BD_LEVEL', array('gamer'=>$gamer));
				
				// remove cache so "just leveled up" logic is not re-used
				$gamer->removeProfileCache();
			}

		} catch (Exception $exception) {

			if ($logRecord->id) {
				$logRecord->status = 'failed';
				$logRecord->save();
			}
			self::_handleException($exception, $this->_request);

		}
	}

	/**
	 * Override so we can attach the gamer profile to the request
	 *
	 */
	public function loadGamerProfile () {
		parent::loadGamerProfile();
		$this->_request->setParam(Api_AbstractController::GAME, $this);
	}

	/**
	 * Create a new Starbar "Game"
	 *
	 * The game is determined in Game_Factory::create from the starbar economy,
	 * so we need to make sure the starbar is registered.
	 *
	 * @param Gaming_User $gamer
	 * @param Zend_Controller_Request_Abstract $request
	 * @return Game_Starbar | NullObject
	 */
	public static function create (Gaming_User $gamer, Zend_Controller_Request_Abstract $request, Starbar $starbar = null) {

		try {
			if (!Registry::isRegistered('starbar')) {
				$starbar = new Starbar();
				$starbarId = $request->getParam('starbar_id');
				$shortName = $request->getParam('short_name');
				if ($starbarId) {
					// 2b. via starbar_id
					$starbar->loadData($starbarId);
				} else if ($shortName) {
					// 2c. via short_name
					$starbar->loadDataByUniqueFields(array('short_name' => $shortName));
				} else {
					// 2d. via Starbar_<shortname>Controller
					$shortName = strtolower($request->getControllerName());
					$starbar->loadDataByUniqueFields(array('short_name' => $shortName));
					if (!$starbar->hasId()) {
						throw new Api_Exception(Api_Error::create(Api_Error::GAMING_ERROR, 'Could not determine Game in Game_Starbar::create(). See method for for more information.'));
					}
				}
			}

			return Game_Factory::create($gamer, $request);

		} catch (Exception $exception) {

			self::_handleException($exception, $request);

		}
		return new NullObject('Game_Starbar');
	}

	/**
	 * Get the single Game instance for this request
	 *
	 * @return Game_Starbar
	 */
	public static function getInstance () {
		static $game = null;
		if (Game_Abstract::$_enabled) {
			if (!$game) {
				$request = Zend_Controller_Front::getInstance()->getRequest();

				$gamer = Gamer::create($request->getParam('user_id'), $request->getParam('starbar_id'));
				$game = Game_Starbar::create($gamer, $request);
			}
			return $game;
		} else {
			return new NullObject('Game_Starbar');
		}
	}

	/**
	 * Handle game exceptions
	 * - in production, they should be supressed
	 *   but also logged
	 * - in development, they should bubble up
	 * - see notes inline for more info how to control
	 *   whether they show up or not in either env.
	 *
	 * @param Exception $exception
	 * @param Zend_Controller_Request_Abstract $request
	 * @throws Exception
	 */
	protected static function _handleException (Exception $exception, Zend_Controller_Request_Abstract $request) {

		$debugGame = $request->getParam('debug_game');
		// on local dev: throw game exceptions -- use debug_game=false to supress exceptions
		// on live: supress exceptions -- use debug_game=true to throw exceptions
		if (($debugGame || APPLICATION_ENV === 'development' || APPLICATION_ENV === 'sandbox') && $debugGame !== 'false') {
			throw $exception;
		}
		// because Api_Exception unregisters the renderer, we need to restore it here
		if ($exception instanceof Api_Exception) {
			$exception->restoreRenderer();
		}
		// log errors regardless
		Api_Error::log($exception, $request);
	}

	 public function getGoodsFromStore() {

		$goodsData = null;
		$cache = Api_Cache::getInstance('BigDoor_getNamedTransactionGroup_store_' . $this->getEconomy()->getKey(), Api_Cache::LIFETIME_WEEK);

		if ($cache->test()) {

			$goodsData = $cache->load();

		} else {

			$client = $this->getHttpClient();
			$client->setCustomParameters(array(
				'attribute_friendly_id' => 'bdm-product-variant',
				'verbosity' => 9,
				'max_records' => 200
			));

			$client->getNamedTransactionGroup('store');
			$goodsData = $client->getData();
			$cache->save($goodsData);

		}

		return $goodsData;
	}


	protected static function setStaticProfileSurveyVariables ($request) {
		if (self::$userHasCompletedProfileSurvey !== null && self::$profileSurveyId !== null) return;

		$profileSurvey = new Survey();
		$profileSurvey->loadProfileSurveyForStarbar((int) $request->getParam('starbar_id'));
		self::$profileSurveyId = $profileSurvey->id;
		self::$userHasCompletedProfileSurvey = ($profileSurvey->id ? Survey_Response::checkIfUserHasCompletedSurvey((int) $request->getParam('user_id'), $profileSurvey->id) : true);
	}
	public function purchaseGood (Gaming_BigDoor_Good $good, $quantity = 1) {
		parent::purchaseGood($good, $quantity);
		Game_Transaction::purchaseBDGood($this->getGamer(false), $good, $quantity);
	}
}
