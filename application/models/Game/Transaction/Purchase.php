<?php
class Game_Transaction_Purchase extends Game_Transaction {
	public function execute() {
		$asset_id = $this->_parameters['asset_id'];
		$good = $this->_economy->_purchasables[$asset_id];
		$price = $good['price'];
		if( array_key_exists('quantity', $this->_parameters) )
			$quantity = $this->_parameters['quantity'];
		else
			$quantity = 1;
		$currency_id = $this->_economy->redeemable_currency_id;
			
		$sql = 'INSERT INTO game_transaction (game_transaction_type_id, user_id, parameters) VALUES (?, ?, ?)';
		Db_Pdo::execute($sql, $this->_transaction_type['id'], $this->_user_id, json_encode($this->_parameters));
		$transaction_id = Db_Pdo::getPdo()->lastInsertId();
		$sql = 'INSERT INTO game_transaction_line (game_transaction_id, game_asset_id, amount) VALUES (?, ?, ?)';
		Db_Pdo::execute($sql, $transaction_id, $asset_id, $quantity );
		Db_Pdo::execute($sql, $transaction_id, $currency_id, - $quantity * $price );
	}
}
