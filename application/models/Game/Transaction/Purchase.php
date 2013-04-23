<?php
class Game_Transaction_Purchase extends Game_Transaction {
	protected $_purchasable, $_quantity;
	public function canRun() {
		if( array_key_exists('quantity', $this->_parameters) )
			$this->_quantity = $this->_parameters['quantity'];
		else
			$this->_quantity = 1;
		$this->_purchasable = Game_Transaction::getPurchasableForUser($this->_user_id
			, $this->_parameters['starbar_id'], $this->_parameters['game_asset_id'], $this->_quantity);
		$this->_status_code = $this->_purchasable->status_code;
		$this->_status_msg = $this->_purchasable->cant_purchase_message;
		return $this->_status_code;
	}
	public function execute() {
		$price = $this->_purchasable->price;
		$redeemable_id = $this->_economy->getCurrencyIdByTypeId(Economy::CURRENCY_REDEEMABLE);
		$tracking_purchase_id = $this->_economy->getCurrencyIdByTypeId(Economy::CURRENCY_TRACKING_PURCHASE);
		$tracking_token_id = $this->_economy->getCurrencyIdByTypeId(Economy::CURRENCY_TRACKING_TOKEN);
		
		if( array_key_exists('starbar_id', $this->_parameters) )
			unset( $this->_parameters['starbar_id'] );
			
		$sql = 'INSERT INTO game_transaction (game_transaction_type_id, user_id, parameters) VALUES (?, ?, ?)';
		Db_Pdo::execute($sql, $this->_transaction_type['id'], $this->_user_id, json_encode($this->_parameters));
		$transaction_id = Db_Pdo::getPdo()->lastInsertId();
		
		$lines[] = array('game_asset_id'=>$this->_purchasable->id, 'amount'=>$this->_quantity);
		$lines[] = array('game_asset_id'=>$redeemable_id, 'amount'=>- $this->_quantity * $price);
		$lines[] = array('game_asset_id'=>$tracking_purchase_id, 'amount'=>$this->_quantity);
		if( $this->_purchasable->type == 'token' )
			$lines[] = array('game_asset_id'=>$tracking_token_id, 'amount'=>$this->_quantity);
		$this->_saveLines($transaction_id, $lines);
		return $transaction_id;
	}
}
