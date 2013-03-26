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
			$transaction->execute();
			if( $user_id && $user_id != self::HOUSE_USER_ID && $short_name != 'LEVEL_UP' )
				self::checkUserLevel($economy, $user_id, $parameters);
		} catch ( Exception $e ) {
			self::_handleException( $e );
		}
	}
	static public function checkUserLevel( Economy $economy, $user_id, $parameters ) {
		$level = self::getBalance($user_id, $economy->_level_asset_id);
		$experience = self::getBalance($user_id, $economy->getCurrencyIdByTypeId(Economy::CURRENCY_EXPERIENCE));
		
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
			$sql = "INSERT INTO game_asset (economy_id, type, name, bdid) VALUES (?, 'purchasable', ?, ?)";
			Db_Pdo::execute($sql, $economy_id, $good_data['description'], $good_data['bdid']);
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
}
