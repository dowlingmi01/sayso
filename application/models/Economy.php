<?php

class Economy extends Record
{
	protected $_tableName = 'economy';
	protected static $_economies = array();
	protected static $_starbars = array();
	public $_currencies, $_purchasables, $_levels, $_bdids, $_currency_types, $_transaction_types, $_level_asset_id;

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
		$currency_types = array();
		$transaction_types = array();
		$sql = "SELECT * FROM game_currency_view WHERE economy_id = ?";
		$res = Db_Pdo::fetchAll($sql, $this->id);
		foreach( $res as $record ) {
			$currencies[$record['id']] = $record;
			$bdids[$record['bdid']] = $record['id'];
			$currency_types[$record['game_currency_type_id']] = $record['id'];
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
		$this->_currency_types = $currency_types;
	}
	public static function getForId( $economy_id ) {
		if( array_key_exists($economy_id, Economy::$_economies) )
			return Economy::$_economies[$economy_id];
		else {
			$economy = new Economy();
			$economy->loadData($economy_id);
			$economy->readData();
			Economy::$_economies[$economy_id] = $economy;
			return $economy;
		}
	}
	public function getCurrencyByBDId( $bdid ) {
		if( !array_key_exists($bdid, $this->_bdids) )
			throw new Exception('Unknown Currency BDID ' . $bdid . ' in economy ' . $this->id);
		return $this->_currencies[$this->_bdids[$bdid]];
	}
	public function getCurrencyIdByTypeId( $currency_type_id ) {
		if( !array_key_exists($currency_type_id, $this->_currency_types) )
			throw new Exception('Unknown Currency Type ID ' . $currency_type_id . ' in economy ' . $this->id);
		return $this->_currency_types[$currency_type_id];
	}
	public function getCurrencyByTypeId( $currency_type_id ) {
		return $this->_currencies[$this->getCurrencyIdByTypeId($currency_type_id)];
	}
	public function getPurchasableByBDId( $bdid ) {
		if( !array_key_exists($bdid, $this->_bdids) )
			throw new Exception('Unknown Purchasable BDID ' . $bdid . ' in economy ' . $this->id);
		return $this->_purchasables[$this->_bdids[$bdid]];
	}
	public function getPurchasableIdByBDId( $bdid ) {
		if( !array_key_exists($bdid, $this->_bdids) )
			throw new Exception('Unknown Purchasable BDID ' . $bdid . ' in economy ' . $this->id);
		return $this->_bdids[$bdid];
	}
	public static function getIdforStarbar( $starbar_id ) {
		if( !array_key_exists($starbar_id, Economy::$_starbars) ) {
			$sql = "SELECT id, economy_id FROM starbar";
			$res = Db_Pdo::fetchAll($sql);
			foreach( $res as $record )
				Economy::$_starbars[$record['id']] = $record['economy_id'];
		}
		return Economy::$_starbars[$starbar_id];
	}
	const CURRENCY_EXPERIENCE = 1;
	const CURRENCY_REDEEMABLE = 2;
	const CURRENCY_TRACKING_CHECKIN = 3;
	const CURRENCY_TRACKING_CREATION = 4;
	const CURRENCY_TRACKING_INFLUENCE = 5;
	const CURRENCY_TRACKING_MISSION = 6;
	const CURRENCY_TRACKING_MOVIEBYTE = 7;
	const CURRENCY_TRACKING_OPINION = 8;
	const CURRENCY_TRACKING_PERSONAL = 9;
	const CURRENCY_TRACKING_POLL_PREMIUM = 10;
	const CURRENCY_TRACKING_POLL_STANDARD = 11;
	const CURRENCY_TRACKING_PURCHASE = 12;
	const CURRENCY_TRACKING_SOCIAL = 13;
	const CURRENCY_TRACKING_SURVEY_PREMIUM = 14;
	const CURRENCY_TRACKING_SURVEY_PROFILE = 15;
	const CURRENCY_TRACKING_SURVEY_STANDARD = 16;
	const CURRENCY_TRACKING_TENURE = 17;
	const CURRENCY_TRACKING_TOKEN = 18;
	const CURRENCY_TRACKING_TRAILER = 19;
}
