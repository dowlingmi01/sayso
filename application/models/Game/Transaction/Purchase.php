<?php
class Game_Transaction_Purchase extends Game_Transaction {
	public function execute() {
		$bdBalances = $this->_getBDBalances();
		$asset_id = $this->_parameters['asset_id'];
		$good = $this->_economy->_purchasables[$asset_id];
		$price = $good['price'];
		if( array_key_exists('quantity', $this->_parameters) )
			$quantity = $this->_parameters['quantity'];
		else
			$quantity = 1;
		$redeemable_id = $this->_economy->getCurrencyIdByTypeId(Economy::CURRENCY_REDEEMABLE);
		$tracking_purchase_id = $this->_economy->getCurrencyIdByTypeId(Economy::CURRENCY_TRACKING_PURCHASE);
		$tracking_token_id = $this->_economy->getCurrencyIdByTypeId(Economy::CURRENCY_TRACKING_TOKEN);
			
		$sql = 'INSERT INTO game_transaction (game_transaction_type_id, user_id, parameters) VALUES (?, ?, ?)';
		Db_Pdo::execute($sql, $this->_transaction_type['id'], $this->_user_id, json_encode($this->_parameters));
		$transaction_id = Db_Pdo::getPdo()->lastInsertId();
		
		$lines[] = array('game_asset_id'=>$asset_id, 'amount'=>$quantity);
		$lines[] = array('game_asset_id'=>$redeemable_id, 'amount'=>- $quantity * $price);
		$lines[] = array('game_asset_id'=>$tracking_purchase_id, 'amount'=>1);
		if( $good['type'] == 'token' )
			$lines[] = array('game_asset_id'=>$tracking_token_id, 'amount'=>1);
		$this->_saveLines($transaction_id, $lines, $bdBalances);
	}
}
