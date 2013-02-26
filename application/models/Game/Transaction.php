<?php
class Game_Transaction {
	const HOUSE_USER_ID = 0;
	
	protected $_economy, $_transaction_type, $_user_id, $_parameters, $_gamer, $_survey_id;
	
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
	public function execute() {
		$currencies = array();
		if( $this->_gamer ) {
			foreach( $this->_gamer->_currencies as $currency )
				if( $currency->currency_type) {
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
		
		$sql = 'SELECT * FROM game_transaction_type_line WHERE game_transaction_type_id = ?';
		$transaction_type_lines = Db_Pdo::fetchAll($sql, $this->_transaction_type['id']);
		$sql = 'INSERT INTO game_transaction (game_transaction_type_id, user_id, survey_id) VALUES (?, ?, ?)';
		Db_Pdo::execute($sql, $this->_transaction_type['id'], $this->_user_id, $this->_survey_id );
		$transaction_id = Db_Pdo::getPdo()->lastInsertId();
		$sql = 'INSERT INTO game_transaction_line (game_transaction_id, game_asset_id, amount, previous_balance_bd, current_balance_bd) VALUES (?, ?, ?, ?, ?)';
		foreach( $transaction_type_lines as $transaction_type_line ) {
			if( array_key_exists($transaction_type_line['game_asset_id'], $currencies) ) {
				$previous_balance_bd = $currencies[$transaction_type_line['game_asset_id']]['previous'];
				$current_balance_bd = $currencies[$transaction_type_line['game_asset_id']]['current'];
			} else {
				$previous_balance_bd = null;
				$current_balance_bd = null;
			}
			Db_Pdo::execute($sql, $transaction_id, $transaction_type_line['game_asset_id'], $transaction_type_line['amount'], $previous_balance_bd, $current_balance_bd );
		}
	}
	static public function run($user_id, $economy_id, $short_name, $parameters = array()) {
		try {
			$economy = Economy::getForId($economy_id);
			if( !$economy->imported )
				return;
			$transaction_type = $economy->_transaction_types[$short_name];
			$transactionClass = 'Game_Transaction' . ($transaction_type['class'] ? '_' . $transaction_type['class'] : '');
			$transaction = new $transactionClass($transaction_type, $user_id, $parameters);
			$transaction->execute();
			if( $user_id && $user_id != self::HOUSE_USER_ID && $short_name != 'LEVEL_UP' )
				self::checkUserLevel($economy, $user_id, $parameters);
		} catch ( Exception $e ) {
			self::_handleException( $e );
		}
	}
	static public function checkUserLevel( Economy $economy, $user_id, $parameters ) {
		$sql = 'SELECT credits - debits as balance FROM game_balance WHERE user_id = ? AND game_asset_id = ?';
		$res = Db_Pdo::fetchAll($sql, $user_id, $economy->_level_asset_id);
		$level = $res[0]['balance'];
		
		$sql = 'SELECT credits - debits as balance FROM game_balance WHERE user_id = ? AND game_asset_id = ?';
		$res = Db_Pdo::fetchAll($sql, $user_id, $economy->experience_currency_id);
		$experience = $res[0]['balance'];
		
		if( array_key_exists('gamer', $parameters) )
			$parameters = array( 'gamer' => $parameters['gamer'] );
		else
			$parameters = array();
		
		$threshold = $economy->_levels[$level+1]['threshold'];
		if( $experience >= $threshold )
			self::run($user_id, $economy->id, 'LEVEL_UP', $parameters);
	}
	static public function importBDEconomy($economy_id) {
		$economy = new Economy();
		$economy->loadData($economy_id);
		$economyName = $economy->name;
		
		$bigDoorEconomyConfig = APPLICATION_PATH . '/../../library/Gaming/BigDoor/config/' . $economyName . '.xml';
		if (!is_readable($bigDoorEconomyConfig)) {
			DebugBreak();
			throw new Exception('BigDoor configuration/economy (' . $economyName . ') file missing from Gaming/BigDoor/config. Unable to create economy.');
		}
		$economyMap = simplexml_load_file($bigDoorEconomyConfig);
		$currency_ids = array();
		$bd_currency_ids = array();
		foreach($economyMap->currencies->currency as $currency) {
			if( $currency->currency_type ) {
				$sql = "INSERT INTO game_asset (economy_id, type, name, bdid) VALUES (?, 'currency', ?, ?)";
				Db_Pdo::execute($sql, $economy_id, $currency->name, $currency->id );
				$currency_id = Db_Pdo::getPdo()->lastInsertId();
				$currency_ids[$currency->currency_type.''] = $currency_id;
				$bd_currency_ids[$currency->currency_type.''] = $currency->id;
			}
		}
		foreach($economyMap->actions->action as $action) 
			if( $action->experience_points ) {
				$sql = "INSERT INTO game_transaction_type (economy_id, short_name, class, name) VALUES (?, ?, '', '')";
				Db_Pdo::execute($sql, $economy_id, $action->name);
				$transaction_type_id = Db_Pdo::getPdo()->lastInsertId();
				$sql = "INSERT INTO game_transaction_type_line (game_transaction_type_id, game_asset_id, amount) VALUES (?, ?, ?)";
				Db_Pdo::execute($sql, $transaction_type_id, $currency_ids['experience'], $action->experience_points);
				Db_Pdo::execute($sql, $transaction_type_id, $currency_ids['redeemable'], $action->redeemable_points);
			}

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
		
		foreach( $goodsData as $goodData )  {
			$good = new Gaming_BigDoor_Good();
			$good->setPrimaryCurrencyId($bd_currency_ids['redeemable']);
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
		
		$economy->redeemable_currency_id = $currency_ids['redeemable'];
		$economy->experience_currency_id = $currency_ids['experience'];
		$economy->imported = new Zend_Db_Expr('now()');
		$economy->save();
		
		foreach( $stock as $stockItem )
			self::run(self::HOUSE_USER_ID, $economy_id, 'IMPORT_BD_STOCK', $stockItem);
	}
	public static function purchaseBDGood(Gamer $gamer, Gaming_BigDoor_Good $good, $quantity) {
		try {
			if( $gamer->imported ) {
				$ggood = Economy::getForId($gamer->starbar_id)->getPurchasableByBDId($good->id);
				Game_Transaction::run($gamer->user_id, $gamer->starbar_id, 'PURCHASE', array('asset_id' => $ggood['id'], 'quantity' => $quantity));
			}
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
}