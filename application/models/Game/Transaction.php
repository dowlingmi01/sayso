<?php
class Game_Transaction {
	const HOUSE_USER_ID = 0;
	const OK = 0;
	const ERROR_INSUFICIENT_CREDIT = 1;
	const ERROR_ALREADY_PURCHASED = 2;
	const ERROR_OUT_OF_STOCK = 3;
	const ERROR_INVALID_QUANTITY = 4;
	const ERROR_ASSET_DISABLED = 5;
	const ERROR_SURVEY_REQUIREMENT = 5;
	
	protected $_economy, $_transaction_type, $_user_id, $_parameters, $_gamer, $_survey_id;
	
	protected $_status_code, $_status_msg;

	protected static $_profile_survey = null;
	
	protected static $_transaction_executed = false;
	
	public function __construct($transaction_type, $user_id, $parameters) {
		$this->_transaction_type = $transaction_type;
		$this->_economy = Economy::getForId($transaction_type['economy_id']);
		$this->_user_id = $user_id;
		if( array_key_exists('gamer', $parameters) ) {
			$this->_gamer = $parameters['gamer'];
			unset($parameters['gamer']);
		}
		if( array_key_exists('survey_id', $parameters) ) {
			$this->_survey_id = $parameters['survey_id'];
			unset($parameters['survey_id']);
		}
		$this->_parameters = $parameters;
	}
	protected function _getBDBalances() {
		$currencies = array();
		if( $this->_gamer ) {
			foreach( $this->_gamer->_currencies as $currency ) {
				$asset = $this->_economy->getCurrencyByBDId($currency->id);
				$currencies[$asset['id']]['previous'] = $currency->previous_balance;
				$currencies[$asset['id']]['current'] = $currency->current_balance;
			}
			$current_level_bd = $this->_gamer->_levels->count();
			if( $this->_gamer->justLeveledUp() )
				$previous_level_bd = $current_level_bd - 1;
			else
				$previous_level_bd = NULL;
			$currencies[$this->_economy->_level_asset_id] = array( 'current' => $current_level_bd, 'previous' => $previous_level_bd );
		}
		
		return $currencies;
	}
	protected function _saveLines( $transaction_id, $lines ) {
		$bdBalances = $this->_getBDBalances();
		$sql = 'INSERT INTO game_transaction_line (game_transaction_id, game_asset_id, amount, previous_balance_bd, current_balance_bd) VALUES (?, ?, ?, ?, ?)';
		foreach( $lines as $line ) {
			if( array_key_exists($line['game_asset_id'], $bdBalances) ) {
				$previous_balance_bd = $bdBalances[$line['game_asset_id']]['previous'];
				$current_balance_bd = $bdBalances[$line['game_asset_id']]['current'];
			} else {
				$previous_balance_bd = null;
				$current_balance_bd = null;
			}
			Db_Pdo::execute($sql, $transaction_id, $line['game_asset_id'], $line['amount'], $previous_balance_bd, $current_balance_bd );
		}
		
	}
	public function execute() {
		$sql = 'SELECT * FROM game_transaction_type_line WHERE game_transaction_type_id = ?';
		$lines = Db_Pdo::fetchAll($sql, $this->_transaction_type['id']);
		$sql = 'INSERT INTO game_transaction (game_transaction_type_id, user_id, survey_id) VALUES (?, ?, ?)';
		Db_Pdo::execute($sql, $this->_transaction_type['id'], $this->_user_id, $this->_survey_id );
		$transaction_id = Db_Pdo::getPdo()->lastInsertId();
		$this->_saveLines($transaction_id, $lines);
		return $transaction_id;
	}
	public function canRun() {
		return self::OK;
	}
	static public function run($user_id, $economy_id, $short_name, $parameters = array()) {
		try {
			$economy = Economy::getForId($economy_id);
			if( !$economy->imported )
				return;
			$transaction_type = $economy->_transaction_types[$short_name];
            if( !$transaction_type )
                throw new Exception('Unknown transaction_type ' . $short_name . ' in economy ' . $economy_id);
			$transactionClass = 'Game_Transaction' . ($transaction_type['class'] ? '_' . $transaction_type['class'] : '');
			$transaction = new $transactionClass($transaction_type, $user_id, $parameters);
			if( ($errorCode = $transaction->canRun()) == self::OK ) {
				$transaction_id = $transaction->execute();
				self::$_transaction_executed = true;
				if( $user_id && $user_id != self::HOUSE_USER_ID && $short_name != 'LEVEL_UP' )
					self::checkUserLevel($economy, $user_id, $parameters);
				return $transaction_id;
			} else
				throw new Exception('Transaction validation error ' . $errorCode);
		} catch ( Exception $e ) {
			self::_handleException( $e );
		}
	}
	static public function checkUserLevel( Economy $economy, $user_id, $parameters ) {
		$level = self::getBalance($user_id, $economy->_level_asset_id);
		$experience = self::getBalance($user_id, $economy->getCurrencyIdByTypeId(Economy::CURRENCY_EXPERIENCE));
		
		if( array_key_exists( $level+1, $economy->_levels ) ) {
			$threshold = $economy->_levels[$level+1]['threshold'];
			if( $experience >= $threshold ) {
				if( array_key_exists('gamer', $parameters) )
					$parameters = array( 'gamer' => $parameters['gamer'] );
				else
					$parameters = array();
				self::run($user_id, $economy->id, 'LEVEL_UP', $parameters);
			}
		}
	}
	static public function importBDEconomy($economy_id) {
		$economy = new Economy();
		$economy->loadData($economy_id);
		$economyName = $economy->name;
		
		$bigDoorEconomyConfig = APPLICATION_PATH . '/../../library/Gaming/BigDoor/config/' . $economyName . '.xml';
		if (!is_readable($bigDoorEconomyConfig)) {
			throw new Exception('BigDoor configuration/economy (' . $economyName . ') file missing from Gaming/BigDoor/config. Unable to create economy.');
		}
		$economyMap = simplexml_load_file($bigDoorEconomyConfig);

		// Import Goods Store	
		$goodsData = null;
		$cache = Api_Cache::getInstance('BigDoor_getNamedTransactionGroup_store_' . $economyMap->key, Api_Cache::LIFETIME_WEEK);
		
		$stock = array();
		if ($cache->test())
			$goodsData = $cache->load();
		else {
			$client = Gaming_BigDoor_HttpClient::getInstance($economyMap->key, $economyMap->secret);
			
			$client->setCustomParameters(array(
				'attribute_friendly_id' => 'bdm-product-variant',
				'verbosity' => 9,
				'max_records' => 200
			));

			$client->getNamedTransactionGroup('store');
			$goodsData = $client->getData();
			$cache->save($goodsData);
		}
		
		$sql = "SELECT * FROM game_currency_view WHERE economy_id = ? AND game_currency_type_id = ?";
		$res = Db_Pdo::fetchAll($sql, $economy_id, Economy::CURRENCY_REDEEMABLE);
		$redeemable_bdid = $res[0]['bdid'];
		
		foreach( $goodsData as $goodData )  {
			$good = new Gaming_BigDoor_Good();
			$good->setPrimaryCurrencyId($redeemable_bdid);
			$good->build($goodData);
			if( $good->isForCurrentEnvironment() ) {
				$goods[] = $good;
				$sql = "INSERT INTO game_asset (economy_id, type, name, bdid) VALUES (?, 'purchasable', ?, ?)";
				Db_Pdo::execute($sql, $economy_id, $good->description, $good->getId());
				$asset_id = Db_Pdo::getPdo()->lastInsertId();
				$sql = "INSERT INTO game_purchasable (game_asset_id, type, price) VALUES (?, ?, ?)";
				Db_Pdo::execute($sql, $asset_id, $good->hasAttribute('giveaway_token') ? 'token' : 'physical', $good->cost);
				$stock[] = array('asset_id'=>$asset_id, 'sold'=>$good->inventory_sold, 'total'=>$good->inventory_total);
			}
		}
		
		// Import Levels	
		$levelCollectionId = $economyMap->levels->level->id;
		$cache = Api_Cache::getInstance('BigDoor_namedLevelCollection_' . $levelCollectionId, Api_Cache::LIFETIME_WEEK);
		if ($cache->test()) {
			$levels = $cache->load();
		} else {
			$client = Gaming_BigDoor_HttpClient::getInstance($economyMap->key, $economyMap->secret);
			
			$client->getNamedLevelCollection($levelCollectionId);
			$data = $client->getData();
			$levels = new Collection();
			foreach ($data->named_levels as $levelData) {
				$level = new Gaming_BigDoor_Level();
				$level->setId($levelData->id);
				$level->title = $levelData->end_user_title;
				$level->description = $levelData->end_user_description;
				$level->urls = Gaming_BigDoor_Url::buildUrlCollection($levelData->urls);
				$level->timestamp = $levelData->created_timestamp;
				$level->ordinal = $levelData->threshold;
				$levels[] = $level;
			}
			$cache->save($levels);
		}
			
		$levels->orderBy('ordinal');
		
		$sql = "SELECT * FROM game_asset WHERE economy_id = ? AND type = 'level'";
		$res = Db_Pdo::fetchAll($sql, $economy_id);
		$asset_id = $res[0]['id'];
		$ord = 1;
		foreach( $levels as $level ) {
			$sql = "INSERT INTO game_level (game_asset_id, ordinal, threshold, name, description) VALUES (?, ?, ?, ?, ?)";
			Db_Pdo::execute($sql, $asset_id, $ord, $level->ordinal, $level->title, $level->description ? $level->description : '');
			$ord++;
		}
		
		$economy->imported = new Zend_Db_Expr('now()');
		$economy->save();
		
		foreach( $stock as $stockItem )
			self::run(self::HOUSE_USER_ID, $economy_id, 'IMPORT_BD_STOCK', $stockItem);
	}
	public static function purchaseBDGood(Gamer $gamer, Gaming_BigDoor_Good $good, $quantity) {
		try {
			if( $gamer->imported ) {
				$ggood = Economy::getForId($gamer->starbar_id)->getPurchasableByBDId($good->id);
				Game_Transaction::run($gamer->user_id, $gamer->starbar_id, 'PURCHASE', array('asset_id' => $ggood['id'], 'quantity' => $quantity, 'gamer' => $gamer));
			}
		} catch ( Exception $e ) {
			self::_handleException( $e );
		}
	}
	public static function addGood( $economy_id, $good_data, $initial_stock ) {
		try {
			if( !Economy::getForId($economy_id)->imported )
				return;
			$sql = "INSERT INTO game_asset (economy_id, type, name, bdid, img_url, img_url_preview, img_url_preview_bought) VALUES (?, 'purchasable', ?, ?, ?, ?, ?)";
			Db_Pdo::execute($sql, $economy_id, $good_data['description'], $good_data['bdid'], $good_data['img_url'], $good_data['img_url_preview'], $good_data['img_url_preview_bought']);
			$asset_id = Db_Pdo::getPdo()->lastInsertId();
			$sql = "INSERT INTO game_purchasable (game_asset_id, type, price) VALUES (?, ?, ?)";
			Db_Pdo::execute($sql, $asset_id, $good_data['type'], $good_data['cost']);
			if( $good_data['type'] != 'token') {
				$stock = array('asset_id'=>$asset_id, 'quantity'=>$initial_stock);
				self::run(self::HOUSE_USER_ID, $economy_id, 'INITIAL_STOCK', $stock);
			}
		} catch ( Exception $e ) {
			self::_handleException( $e );
		}
	}
	public static function adjustBDStock( $economy_id, $good_bdid, $quantity ) {
		try {
			if( !Economy::getForId($economy_id)->imported )
				return;
			$stock = array('asset_id'=>Economy::getForId($economy_id)->getPurchasableIdByBDId($good_bdid), 'quantity'=>$quantity);
			self::run(self::HOUSE_USER_ID, $economy_id, 'ADJUST_STOCK', $stock);
		} catch ( Exception $e ) {
			self::_handleException( $e );
		}
	}
	protected static function _handleException (Exception $exception) {
		// because Api_Exception unregisters the renderer, we need to restore it here
		if ($exception instanceof Api_Exception) {
			$exception->restoreRenderer();
		}
		$message = $exception->__toString();
		static $logger;
		if( !$logger ) {
			$logger = new Zend_Log();
			$logWriter = new Zend_Log_Writer_Stream(realpath(APPLICATION_PATH . '/../log') . '/game.log');
			$logger->addWriter($logWriter);
		}
		$logger->log($message, Zend_Log::INFO);
	}
	public static function getBalance($user_id, $asset_id) {
		$sql = 'SELECT credits - debits as balance FROM game_balance WHERE user_id = ? AND game_asset_id = ?';
		$res = Db_Pdo::fetchAll($sql, $user_id, $asset_id);
		return count($res) ? $res[0]['balance'] : 0;
	}
	public static function getBalances($user_id, $economy_id) {
		$sql = 'SELECT a.id, credits - debits as balance FROM game_balance b, game_asset a
		         WHERE b.game_asset_id = a.id AND user_id = ? AND economy_id = ?';
		$res = Db_Pdo::fetchAll($sql, $user_id, $economy_id);
		$balances = array();
		foreach($res as $balance)
			$balances[$balance['id']] = intval($balance['balance']);
		return $balances;
	}
	public static function getGame( $user_id, $economy_id ) {
		$economy = Economy::getForId($economy_id);
		$balances = self::getBalances($user_id, $economy_id);
		$purchasables = array();
		foreach($economy->_purchasables as $id=>$purchasable) {
			if( array_key_exists($id, $balances) && $balances[$id] > 0 ) {
				$purchasables[$id] = $purchasable;
				$purchasables[$id]['quantity'] = $balances[$id];
			}
		}
		$currencies = array(
			'redeemable'=>$economy->getCurrencyByTypeId(Economy::CURRENCY_REDEEMABLE),
			'experience'=>$economy->getCurrencyByTypeId(Economy::CURRENCY_EXPERIENCE)
		);
		$currencies['redeemable']['balance'] = $balances[$currencies['redeemable']['id']];
		$currencies['experience']['balance'] = $balances[$currencies['experience']['id']];
		$res = array(
			'purchasables'=>$purchasables,
			'currencies'=>$currencies,
			'level'=>intval($balances[$economy->_level_asset_id]),
			'levels'=>$economy->_levels,
			'max_level'=>count($economy->_levels)
		);
		return $res;
	}
	public static function getUserLevelName( $user_id, $starbar_id ) {
		$economy = Economy::getForId(Economy::getIdforStarbar($starbar_id));
		$level = self::getBalance($user_id, $economy->_level_asset_id);
		return $economy->_levels[$level]['name'];
	}
	public static function addGameToRequest( $request ) {
		$request->setParam(Api_AbstractController::GAME, self::getGame($request->getParam('user_id'), Economy::getIdforStarbar($request->getParam('starbar_id'))));
	}
	public static function completeSurvey( $user_id, $starbar_id, Survey $survey ) {
		$economy_id = Economy::getIdforStarbar($starbar_id);
		if( $survey->custom_reward_experience && $survey->custom_reward_redeemable ) {
			self::run($user_id, $economy_id, 'ADHOC_EXPERIENCEPOINTS', array('survey_id'=>$survey->id, 'custom_amount'=>$survey->custom_reward_experience));
			self::run($user_id, $economy_id, 'ADHOC_REDEEMABLEPOINTS', array('survey_id'=>$survey->id, 'custom_amount'=>$survey->custom_reward_redeemable));
		} else if( in_array($survey->type, array('survey', 'poll', 'quiz', 'trailer')) && in_array($survey->reward_category, array('standard', 'premium', 'profile')) ) {
			// POLL_STANDARD, SURVEY_PROFILE, QUIZ_PREMIUM, etc.
			self::run($user_id, $economy_id, strtoupper($survey->type.'_'.$survey->reward_category), array('survey_id'=>$survey->id));
		}
	}
	public static function disqualifySurvey( $user_id, $starbar_id, Survey $survey ) {
		$economy_id = Economy::getIdforStarbar($starbar_id);
		// POLL_STANDARD_DISQUALIFIED, SURVEY_PROFILE_DISQUALIFIED, QUIZ_PREMIUM_DISQUALIFIED, etc.
		if (in_array($survey->type, array('survey', 'poll', 'quiz', 'trailer')) && in_array($survey->reward_category, array('standard', 'premium', 'profile'))) {
			$this->submitAction(strtoupper($survey->type.'_'.$survey->reward_category).'_DISQUALIFIED', 0, $survey->id);
			self::run($user_id, $economy_id, strtoupper($survey->type.'_'.$survey->reward_category).'_DISQUALIFIED', array('survey_id'=>$survey_id));
		}
	}
	public function associateSocialNetwork( $user_id, $starbar_id, User_Social $userSocial ) {
		$economy_id = Economy::getIdforStarbar($starbar_id);
		switch ($userSocial->provider) {
			case 'facebook' :
				self::run($user_id, $economy_id, 'FACEBOOK_ASSOCIATE');
				break;
			case 'twitter' :
				self::run($user_id, $economy_id, 'TWITTER_ASSOCIATE');
				break;
			default :
				throw new Api_Exception(Api_Error::create(Api_Error::GAMING_ERROR, 'Wrong or missing social user provider (' . $userSocial->provider . ') supplied to Game_Transaction::associateSocialNetwork().'));
				break;
		}
	}
	static public function share( $user_id, $starbar_id, $type, $network, $typeId = 0 ) {
		$economy_id = Economy::getIdforStarbar($starbar_id);
		$network = strtoupper($network);

		if (!in_array($network, array("FB", "TW"))) {
			throw new Api_Exception(Api_Error::create(Api_Error::GAMING_ERROR, 'Cannot award user. No social network specified.'));
		}
                
		$shareString = "";
		$parameters = array();

		switch( $type ) {
			case 'poll' :
			case 'survey' :
			case 'quiz' :
			case 'trailer' :
				if (!$typeId) {
					throw new Api_Exception(Api_Error::create(Api_Error::GAMING_ERROR, 'Cannot award user for sharing survey/poll/quiz/trailer. Survey id missing from call and required in order to determine if standard or premium.'));
				}
				$parameters['survey_id'] = $typeId;
				$survey = new Survey();
				$survey->loadData($typeId);
				// e.g. FB_QUIZ_STANDARD_SHARE, TW_SURVEY_PREMIUM_SHARE, etc.
				$shareString = strtoupper($network.'_'.$survey->type.'_'.$survey->reward_category).'_SHARE';
				break;
			case 'starbar' :
				$shareString = $network.'_SHARE_STARBAR';
				break;
			case 'promos' :
				$shareString = $network.'_SHARE_PROMOS';
				break;
			default :
				throw new Api_Exception(Api_Error::create(Api_Error::GAMING_ERROR, 'Wrong type (' . $type . ') supplied to Game_Transaction::share().'));
		}
		self::run($user_id, $economy_id, $shareString, $parameters);
	}
	static public function getPurchasableForUser($user_id, $starbar_id, $game_asset_id, $quantity = 1) {
		$economy_id = Economy::getIdforStarbar($starbar_id);
		$economy = Economy::getForId($economy_id);
		$game = self::getGame($user_id, $economy_id);
		$houseBalances = self::getBalances(self::HOUSE_USER_ID, $economy_id);
		$purchasable = $economy->_purchasables[$game_asset_id];
		$purchasableItem = new Item($purchasable);
		$purchasableItem->stock = array_key_exists($game_asset_id, $houseBalances) ? $houseBalances[$game_asset_id] : 0;
		self::processPurchasable($user_id, $starbar_id, $game, $purchasableItem, $quantity);
		return $purchasableItem;
	}
	static public function getPurchasablesForUser($user_id, $starbar_id) {
		$economy_id = Economy::getIdforStarbar($starbar_id);
		$economy = Economy::getForId($economy_id);
		$game = self::getGame($user_id, $economy_id);
		$houseBalances = self::getBalances(self::HOUSE_USER_ID, $economy_id);
		$purchasables = $economy->_purchasables;
		$purchasablesCollection = new ItemCollection();
		foreach($purchasables as $id=>$purchasable) {
			$purchasableItem = new Item($purchasable);
			$purchasableItem->stock = array_key_exists($id, $houseBalances) ? $houseBalances[$id] : 0;
			self::processPurchasable($user_id, $starbar_id, $game, $purchasableItem, 1);
			if( $purchasableItem->show )
				$purchasablesCollection->addItem($purchasableItem);
		}
		
		$tokens = new ItemCollection();
		$purchased = new ItemCollection();
		$available = new ItemCollection();
		$soldOut = new ItemCollection();
		$results = new ItemCollection();
		$purchasablesCollection->orderBy('name');
		$purchasablesCollection->orderBy('price');
		foreach ($purchasablesCollection as $purchasable) {
				if( $purchasable->type == 'token' ) {
					$tokens->addItem($purchasable);
				} elseif( array_key_exists($purchasable->id, $game['purchasables']) ) {
					$purchased->addItem($purchasable);
				} elseif( $purchasable->stock > 0 ) {
					$available->addItem($purchasable);
				} else {
					$soldOut->addItem($purchasable);
				}
		}
		$tokens->orderBy('id', 'desc'); // reverse the tokens so latest tokens are first
		foreach ($tokens as $purchasable) $results->addItem($purchasable);
		foreach ($available as $purchasable) $results->addItem($purchasable);
		foreach ($purchased as $purchasable) $results->addItem($purchasable);
		foreach ($soldOut as $purchasable) $results->addItem($purchasable);

		return $results;
	}
	static public function isProfileSurveyCompleted( $user_id, $starbar_id ) {
		if( !self::$_profile_survey || $_profile_survey->user_id != $user_id || $_profile_survey->starbar_id != $starbar_id ) {
			self::$_profile_survey = new Object();
			self::$_profile_survey->user_id = $user_id;
			self::$_profile_survey->starbar_id = $starbar_id;

			$profileSurvey = new Survey();
			$profileSurvey->loadProfileSurveyForStarbar($starbar_id);

			self::$_profile_survey->survey_id = $profileSurvey->id;
			self::$_profile_survey->completed = ($profileSurvey->id ? Survey_Response::checkIfUserHasCompletedSurvey($user_id, $profileSurvey->id) : true);
		}
		return self::$_profile_survey->completed;
	}
	static public function getProfileSurveyId( $user_id, $starbar_id ) {
		return self::$_profile_survey->survey_id;
	}
	static public function processPurchasable( $user_id, $starbar_id, $game, $purchasable, $quantity = 1 ) {
		$percentage = round($game['currencies']['redeemable']['balance'] / $purchasable->price * 100);
		if ($percentage > 100)
			$percentage = 100;
		$purchasable->percentage = $percentage;
		$purchasable->can_purchase = false;
		if($purchasable->visible == 'never') {
			$purchasable->status_code = self::ERROR_ASSET_DISABLED;
			$purchasable->show = false;
			return;
		}
		if( !$purchasable->available ) {
			$purchasable->status_code = self::ERROR_ASSET_DISABLED;
			$purchasable->cant_purchase_message = $purchasable->unavailable_message;
			$purchasable->comment = 'Unavailable';
		} else if( $purchasable->type != 'token' && $purchasable->stock < 1 ) {
			$purchasable->status_code = self::ERROR_OUT_OF_STOCK;
			$purchasable->cant_purchase_message = 'Sorry, this item is sold out.';
			$purchasable->comment = 'Sold Out';
		} else if( $purchasable->type != 'token' &&  $quantity > 1 ) {
			$purchasable->status_code = self::ERROR_INVALID_QUANTITY;
			$purchasable->cant_purchase_message = 'Can\'t buy more than one of this item.';
			$purchasable->comment = 'Invalid';
		} else if( $quantity > 5 || $quantity < 1 ) {
			$purchasable->status_code = self::ERROR_INVALID_QUANTITY;
			$purchasable->cant_purchase_message = 'Invalid quantity.';
			$purchasable->comment = 'Invalid';
		} else if( !self::isProfileSurveyCompleted($user_id, $starbar_id) ) {
			$purchasable->status_code = self::ERROR_SURVEY_REQUIREMENT;
			if (self::getProfileSurveyId($user_id, $starbar_id))
				$purchasable->cant_purchase_message = 'Must complete<br /><a href="//'.BASE_DOMAIN.'/starbar/' . '/embed-survey?survey_id='.self::getProfileSurveyId($user_id, $starbar_id).'" class="sb_nav_element" rel="sb_popBox_surveys_hg" title="Take profile survey now!" style="position: relative; top: -5px;">Profile Survey</a>';
			else
				$purchasable->cant_purchase_message = 'Must complete<br />Profile Survey';
			$purchasable->comment = 'Survey Requirement';
		} else if( $game['currencies']['redeemable']['balance'] < $purchasable->price * $quantity ) {
			$purchasable->status_code = self::ERROR_INSUFICIENT_CREDIT;
			$purchasable->cant_purchase_message = 'Earn more ' . $game['currencies']['redeemable']['name'] . ' by<br />completing polls and surveys!';
			$purchasable->comment = 'Insufficient ' . $game['currencies']['redeemable']['name'];
		} else if( $purchasable->type != 'token' && array_key_exists($purchasable->id, $game['purchasables'])) {
			$purchasable->status_code = self::ERROR_ALREADY_PURCHASED;
			$purchasable->cant_purchase_message = 'You have already<br />purchased this item.<br /><br />You can always buy<br />more tokens for the giveaways!<br />';
			$purchasable->comment = 'Purchased';
		} else {
			$purchasable->status_code = self::OK;
			$purchasable->can_purchase = true;
		}

		if( $purchasable->type != 'token' && $purchasable->stock > 0 && $purchasable->stock < 4 && !array_key_exists($purchasable->id, $game['purchasables']))
			$purchasable->comment = 'Only ' . $purchasable->stock . ' left!';
			
		switch( $purchasable->visible ) {
		case 'purchasable':
			$purchasable->show = $purchasable->can_purchase;
			break;
		case 'instock':
			$purchasable->show = $purchasable->available && ($purchasable->type == 'token' || $purchasable->stock > 0);
			break;
		case 'always':
			$purchasable->show = true;
			break;
		}
	}
	static public function wasTransactionExecuted() {
		return self::$_transaction_executed;
	}
}
