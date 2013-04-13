<?php
class Game_Transaction_ImportBDUser extends Game_Transaction {
	public function execute() {
		$currencies = array();
		foreach( $this->_gamer->_currencies as $currency ) {
			$asset = $this->_economy->getCurrencyByBDId($currency->id);
			$currencies[$asset['id']] = $currency->current_balance;
		}
		$nonTokenPresent = false;
		$goods = array();
		foreach( $this->_gamer->_goods as $bdgood ) {
			$asset = $this->_economy->getPurchasableByBDId($bdgood->id);
			$isToken = $asset['type'] == 'token';
			$goods[$asset['id']] = array('balance' => $bdgood->quantity, 'istoken' => $isToken);
			if( !$isToken )
				$nonTokenPresent = true;
		}
		$level_id = $this->_economy->_level_asset_id;
		$level = $this->_gamer->_levels->count();
		
		$parameters = array();
		$parameters['currencies'] = $currencies;
		$parameters['goods'] = $goods;
		$parameters['level'] = $level;
		$sql = 'INSERT INTO game_transaction (game_transaction_type_id, user_id, parameters) VALUES (?, ?, ?)';
		Db_Pdo::execute($sql, $this->_transaction_type['id'], $this->_user_id, json_encode($parameters));
		$transaction_id = Db_Pdo::getPdo()->lastInsertId();
		$sql = 'INSERT INTO game_transaction_line (game_transaction_id, game_asset_id, amount) VALUES (?, ?, ?)';
		foreach( $currencies as $asset_id => $balance )
			Db_Pdo::execute($sql, $transaction_id, $asset_id, $balance );
		foreach( $goods as $asset_id => $good )
			Db_Pdo::execute($sql, $transaction_id, $asset_id, $good['balance'] );
		Db_Pdo::execute($sql, $transaction_id, $level_id, $level );
		if($nonTokenPresent) {
			$sql = 'INSERT INTO game_transaction (game_transaction_type_id, user_id, parameters) VALUES (?, ?, ?)';
			Db_Pdo::execute($sql, $this->_transaction_type['id'], Game_Transaction::HOUSE_USER_ID, json_encode(array('user_id'=>$this->_user_id)));
			$transaction_id = Db_Pdo::getPdo()->lastInsertId();
			$sql = 'INSERT INTO game_transaction_line (game_transaction_id, game_asset_id, amount) VALUES (?, ?, ?)';
			foreach( $goods as $asset_id => $good )
				if( !$good['istoken'])
					Db_Pdo::execute($sql, $transaction_id, $asset_id, $good['balance'] );
		}
		$this->_gamer->imported = new Zend_Db_Expr('now()');
		$this->_gamer->save();
		return $transaction_id;
	}
}
