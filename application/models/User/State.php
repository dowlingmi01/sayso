<?php


class User_State extends Record
{
	protected $_tableName = 'user_state';

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

	static public function getStarbarList( $user_id, $starbar_id = 0 ) {
		//$sql  = "SELECT s.id, s.short_name, s.label FROM starbar_user_map sm, starbar s WHERE s.id = sm.starbar_id AND sm.user_id = ?";
		$sql  = "SELECT s.id, s.short_name, s.label, s.info, sm.active FROM starbar s LEFT JOIN starbar_user_map sm ON (s.id = sm.starbar_id AND sm.user_id = ?) WHERE s.id > 2 ORDER BY active DESC";
		$result = array();
		$res = Db_Pdo::fetchAll($sql, $user_id);
		foreach( $res as $sb )
			if($sb["id"] != $starbar_id && ($sb["active"] || ($sb["id"] != 5 && $starbar_id != 5 && $sb["id"] != 6))) {
				if( $sb["active"] ) {
					$messages = new Notification_MessageCollection();
					$messages->loadAllNotificationMessagesForStarbarAndUser($sb["id"], true, $user_id, null);
					$sb["notifications"] = $messages->count();
				} else
					$sb["notifications"] = 0;
				$result[$sb["id"]] = $sb;
			}
		return $result;
	}
}
