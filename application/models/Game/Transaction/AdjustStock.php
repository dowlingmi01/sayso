<?php
class Game_Transaction_AdjustStock extends Game_Transaction {
	public function execute() {
		$sql = 'SELECT credits - debits AS balance FROM game_balance WHERE user_id = ? AND game_asset_id = ?';
		$res = Db_Pdo::fetchAll($sql, $this->_user_id, $this->_parameters['asset_id']);
		$current_stock = $res[0]['balance'];
		if( $current_stock != $this->_parameters['quantity']) {
			$sql = 'INSERT INTO game_transaction (game_transaction_type_id, user_id, parameters) VALUES (?, ?, ?)';
			Db_Pdo::execute($sql, $this->_transaction_type['id'], $this->_user_id, json_encode($this->_parameters));
			$transaction_id = Db_Pdo::getPdo()->lastInsertId();
			$sql = 'INSERT INTO game_transaction_line (game_transaction_id, game_asset_id, amount) VALUES (?, ?, ?)';
			Db_Pdo::execute($sql, $transaction_id, $this->_parameters['asset_id'], $this->_parameters['quantity'] - $current_stock );
			return $transaction_id;
		}
	}
}
