<?php


class Notification_MessageUserMap extends Record
{
	protected $_tableName = 'notification_message_user_map';

	public function loadMapForNotificationMessageAndUser($messageId, $userId, $excludeClosed = false) {
		$closeClause = "";
		if ($excludeClosed) $closeClause = " AND closed = '0000-00-00 00:00:00' ";
		$sql = "SELECT * FROM notification_message_user_map WHERE notification_message_id = ? AND user_id = ? ".$closeClause." ORDER BY id DESC LIMIT 1";
		$data = Db_Pdo::fetch($sql, $messageId, $userId);
		if ($data) $this->build($data);
	}

	public function updateOrInsertMapForNotificationMessageAndUser($messageId, $userId, $markClosed = false, $markNotified = false) {
		if ($messageId && $userId) {
			// Since we are updating or inserting to the map, exclude mappings that already exist as 'closed'
			// since this instance would be a recurrence of that notification, not the same mapping
			// (i.e. we want to insert a new one if there is already a 'closed' mapping)
			$this->loadMapForNotificationMessageAndUser($messageId, $userId, true);

			// If it's an update, set the closed time (if we are closing) and update the notified time (if it's not set)
			if ($this->id
				&& (
					$markClosed
					|| (!strtotime($this->notified) && $markNotified)
				)
			) {
				if (!strtotime($this->notified) && $markNotified) $this->notified = date('Y-m-d H:i:s');
				if ($markClosed) $this->closed = date('Y-m-d H:i:s');
				$this->save();

			// Otherwise insert!
			} elseif (! $this->id){
				$this->notification_message_id = $messageId;
				$this->user_id = $userId;
				if ($markNotified) $this->notified = date('Y-m-d H:i:s');
				if ($markClosed) $this->closed = date('Y-m-d H:i:s');
				$this->save();
			}
		}
	}
}
