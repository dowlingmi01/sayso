<?php
class Game_Transaction_ImportBDStock extends Game_Transaction {
	public function execute() {
		$total = array_key_exists('total', $this->_parameters) ? $this->_parameters['total'] : 0;
		$total = $total ? $total : 0;
		$sold = array_key_exists('sold', $this->_parameters) ? $this->_parameters['sold'] : 0;
		$sold = $sold ? $sold : 0;
		$balance = $total - $sold;
		if( $balance > 0 ) {
			$sql = 'INSERT INTO game_transaction (game_transaction_type_id, user_id, parameters) VALUES (?, ?, ?)';
			Db_Pdo::execute($sql, $this->_transaction_type['id'], $this->_user_id, json_encode($this->_parameters));
			$transaction_id = Db_Pdo::getPdo()->lastInsertId();
			$sql = 'INSERT INTO game_transaction_line (game_transaction_id, game_asset_id, amount) VALUES (?, ?, ?)';
			Db_Pdo::execute($sql, $transaction_id, $this->_parameters['asset_id'], $balance );
			return $transaction_id;
		}
	}
}
