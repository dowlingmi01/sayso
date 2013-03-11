<?php
class Game_Transaction_AddStock extends Game_Transaction {
	public function execute() {
		$sql = 'INSERT INTO game_transaction (game_transaction_type_id, user_id, parameters) VALUES (?, ?, ?)';
		Db_Pdo::execute($sql, $this->_transaction_type['id'], $this->_user_id, json_encode($this->_parameters));
		$transaction_id = Db_Pdo::getPdo()->lastInsertId();
		$sql = 'INSERT INTO game_transaction_line (game_transaction_id, game_asset_id, amount) VALUES (?, ?, ?)';
		Db_Pdo::execute($sql, $transaction_id, $this->_parameters['asset_id'], $this->_parameters['quantity'] );
	}
}
