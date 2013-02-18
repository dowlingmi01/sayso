<?php

class Economy extends Record
{
	protected $_tableName = 'economy';
	protected static $_economies = array();
	public $_currencies, $_purchasables, $_levels, $_bdids, $_transaction_types, $_level_asset_id;

	public function exportData() {
		$fields = array(
			'title',
			'redeemable_currency',
			'experience_currency',
		);
		return array_intersect_key($this->getData(), array_flip($fields));
	}
	public function readData() {
		$currencies = array();
		$purchasables = array();
		$levels = array();
		$bdids = array();
		$transaction_types = array();
		$sql = "SELECT * FROM game_asset WHERE economy_id = ? AND type = 'currency'";
		$res = Db_Pdo::fetchAll($sql, $this->id);
		foreach( $res as $record ) {
			$currencies[$record['id']] = $record;
			$bdids[$record['bdid']] = $record['id'];
		}
		$sql = "SELECT * FROM game_purchasable_view WHERE economy_id = ?";
		$res = Db_Pdo::fetchAll($sql, $this->id);
		foreach( $res as $record ) {
			$purchasables[$record['id']] = $record;
			$bdids[$record['bdid']] = $record['id'];
		}
		$sql = "SELECT l.* FROM game_level l, game_asset a WHERE l.game_asset_id = a.id AND economy_id = ? AND type = 'level'";
		$res = Db_Pdo::fetchAll($sql, $this->id);
		foreach( $res as $record )
			$levels[$record['ordinal']] = $record;
		
		$sql = "SELECT * FROM game_transaction_type WHERE economy_id = ?";
		$res = Db_Pdo::fetchAll($sql, $this->id);
		foreach( $res as $record )
			$transaction_types[$record['short_name']] = $record;
			
		$this->_bdids = $bdids;
		$this->_currencies = $currencies;
		$this->_levels = $levels;
		$this->_level_asset_id = $levels[1]['game_asset_id'];
		$this->_purchasables = $purchasables;
		$this->_transaction_types = $transaction_types;
	}
	public static function getForId( $economy_id ) {
		if( array_key_exists($economy_id, Economy::$_economies) )
			return Economy::$_economies[$economy_id];
		else {
			$economy = new Economy();
			$economy->loadData($economy_id);
			if($economy->imported) {
				$economy->readData();
				Economy::$_economies[$economy_id] = $economy;
			}
			return $economy;
		}
	}
	public function getCurrencyByBDId( $bdid ) {
		if( !array_key_exists($bdid, $this->_bdids) )
			throw new Exception('Unknown Currency BDID ' . $bdid . ' in economy ' . $this->id);
		return $this->_currencies[$this->_bdids[$bdid]];
	}
	public function getPurchasableByBDId( $bdid ) {
		if( !array_key_exists($bdid, $this->_bdids) )
			throw new Exception('Unknown Purchasable BDID ' . $bdid . ' in economy ' . $this->id);
		return $this->_purchasables[$this->_bdids[$bdid]];
	}
}
