<?php
class Game_Transaction_AdHoc extends Game_Transaction {
	public function execute() {
		if( !array_key_exists('custom_amount', $this->_parameters) )
			parent::execute();
		else {
			$bdBalances = $this->_getBDBalances();
			$sql = 'SELECT * FROM game_transaction_type_line WHERE game_transaction_type_id = ?';
			$lines = Db_Pdo::fetchAll($sql, $this->_transaction_type['id']);
			$lines[0]['amount'] = $this->_parameters['custom_amount'];
			$sql = 'INSERT INTO game_transaction (game_transaction_type_id, user_id, survey_id, parameters) VALUES (?, ?, ?, ?)';
			Db_Pdo::execute($sql, $this->_transaction_type['id'], $this->_user_id, $this->_survey_id, json_encode($this->_parameters));
			$transaction_id = Db_Pdo::getPdo()->lastInsertId();
			$this->_saveLines($transaction_id, $lines, $bdBalances);
		}
	}	
}