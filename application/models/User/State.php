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
}
