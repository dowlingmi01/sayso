<?php


class User_State extends Record
{
	protected $_tableName = 'user_state';

	public function canShare($network, $type, $typeId) {
		switch($network) {
			case "FB":
				$field = "most_recent_fb_share";
				break;
			case "TW":
				$field = "most_recent_tw_share";
				break;
			default:
				return false;
				break;
		}
		$shareString = $type . "-" . $typeId;
		if ($this->$field == $shareString) {
			return false;
		}

		$this->$field = $shareString;
		$this->save();
		return true;
	}

	public function addStarbar( $starbar, $request ) {
		$starbarUserMap = new Starbar_UserMap();
		$starbarUserMap->user_id = $this->user_id;
		$starbarUserMap->starbar_id = $starbar->id;
		$starbarUserMap->active = 1;
		$starbarUserMap->save();

		$isNewGamer = false;
		$gamer = Gamer::create($this->user_id, $starbar->id, $isNewGamer);
		$game = Game_Starbar::create($gamer, $request, $starbar);
		if( $isNewGamer )
			$game->install();

		$this->starbar_id = $starbar->id;
		$this->visibility = "open";
		$this->save();
	}

	public function getStarbarList( $user_id ) {
		//$sql  = "SELECT s.id, s.short_name, s.label FROM starbar_user_map sm, starbar s WHERE s.id = sm.starbar_id AND sm.user_id = ?";
		$sql  = "SELECT s.id, s.short_name, s.label, sm.active FROM starbar s LEFT JOIN starbar_user_map sm ON (s.id = sm.starbar_id AND sm.user_id = ?) WHERE s.id > 1";
		$result = array();
		$res = Db_Pdo::fetchAll($sql, $user_id);
		foreach( $res as $sb )
			$result[$sb["id"]] = $sb;
		return $result;
	}
}
