<?php


class Notification_MessageCollection extends RecordCollection
{
	// === Functions to load notifications messages from their THREE (3) sources:

	/* 1. Notifications based on groups that are on schedules, that the user has viewed and/or closed.
	*     (i.e. notification_message_user_map exists.. if more than group's schedule minimum_interval 
	*     time has passed since the user has closed their most recent message from this group, get next 
	*     message from group if there is one, the first message in the group if there isn't a next one 
	*     and group repeats (loops), or view notification again if it was viewed but never closed)
	*/
	public function loadNextInGroupMessagesForStarbarAndUser ($starbarId, $userId) {
		$messageGroup = new Notification_MessageGroup();
		$messageUserMap = new Notification_MessageUserMap();
		$unfilteredMessages = new Notification_MessageCollection();

		$unfilteredMessages->_loadMostRecentGroupMessagesForStarbarAndUser($starbarId, $userId);
		foreach ($unfilteredMessages as $message) {
			$messageUserMap->loadMapForNotificationMessageAndUser($message->id, $userId);
			$closedStamp = strtotime($messageUserMap->closed);
			if (!$closedStamp) { // message has never been closed (i.e. either never viewed or should remain in view)
				$this->addItem($message); // Append the message to the collection
			} else {
				$messageGroup->loadData($message->notification_message_group_id);
				// The most recently closed notification from that group has been closed longer than the minimum_interval between messages in that group
				if ((time() - $closedStamp) > (int) $messageGroup->minimum_interval) {
					$nextMessage = new Notification_Message();
					// Grab the next message in that group that the user should see
					$nextMessage->loadNextMessageInGroupForUser($message, $messageGroup, $userId);

					// If successful, append it to the collection
					if ($nextMessage->id) {
						$this->addItem($nextMessage);
						// add user_map
    					$messageUserMap = new Notification_MessageUserMap();
    					$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($nextMessage->id, $userId, false);
					}
				}
			}
		}
	}
	
	// For every group that the user has already received messages, get the latest message received
	private function _loadMostRecentGroupMessagesForStarbarAndUser ($starbarId, $userId) {
		$sql = "
			SELECT * FROM (
				SELECT nm.*
				FROM notification_message nm
					INNER JOIN notification_message_group nmg 
						ON nmg.id = nm.notification_message_group_id
							AND nmg.start_at < now()
							AND (nmg.end_at > now() OR nmg.end_at = '0000-00-00 00:00:00')
							AND nmg.starbar_id = ?
							AND nmg.type = 'Scheduled'
					INNER JOIN user 
						ON user.id = ?
						AND ((UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(user.created)) > nmg.start_after
							OR nmg.start_after IS NULL)
					INNER JOIN notification_message_user_map nmum
						ON nmum.notification_message_id = nm.id
							AND nmum.user_id = ?
				ORDER BY nm.notification_message_group_id DESC, nm.ordinal DESC, nmum.id DESC
			) AS S GROUP BY notification_message_group_id
		";
		
		$data = Db_Pdo::fetchAll($sql, $starbarId, $userId, $userId);
		
		if ($data) {
			$this->build($data, new Notification_Message());
		}
	}
	
	/* 2. Notifications based on groups that are on schedules, that the user has never viewed
	*     (i.e. no notification_message_user_map to any notification_message in a scheduled notification_message_group)
	*     We want the first one (by ordinal) from each message group.
	*/
	public function loadPreviouslyUnscheduledMessagesForStarbarAndUser ($starbarId, $userId) {
		$sql = "
			SELECT nm.*
			FROM notification_message nm
				INNER JOIN notification_message_group nmg 
					ON nmg.id = nm.notification_message_group_id
						AND nmg.start_at < now()
						AND (nmg.end_at > now() OR nmg.end_at = '0000-00-00 00:00:00')
						AND nmg.starbar_id = ?
						AND nmg.type = 'Scheduled'
				INNER JOIN user 
					ON user.id = ?
					AND ((UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(user.created)) > nmg.start_after
						OR nmg.start_after IS NULL)
				LEFT OUTER JOIN notification_message_user_map nmum
					ON nmum.notification_message_id = nm.id
						AND nmum.user_id = ?
			WHERE nmum.id IS NULL
			ORDER BY nm.notification_message_group_id DESC, nm.ordinal DESC, nmum.id DESC
		";
		
		$data = Db_Pdo::fetchAll($sql, $starbarId, $userId, $userId);
		
		if ($data) {
			$this->build($data, new Notification_Message());
		}
	}
	
	/* 3. Notifications based on user action (those are notification_message_user_map records 
	*     with an unset 'closed' timestamp ('0000-00-00 00:00:00'))
	*/
    public function loadUserActionMessagesForStarbarAndUser ($starbarId, $userId) {
		$sql = "
			SELECT nm.*
			FROM notification_message nm
				INNER JOIN notification_message_group nmg
					ON nmg.id = nm.notification_message_group_id
						AND nmg.starbar_id = ?
						AND nmg.type = 'User Actions'
				INNER JOIN notification_message_user_map nmum 
					ON nmum.notification_message_id = nm.id
						AND nmum.user_id = ?
						AND closed = '0000-00-00 00:00:00'
			ORDER BY nmum.id DESC
		";
		
        $data = Db_Pdo::fetchAll($sql, $starbarId, $userId);

		if ($data) {
        	$this->build($data, new Notification_Message());
		}
	}
	
	// === Now put them all together (see above), and filter and add user maps when necessary
	public function loadAllNotificationMessagesForStarbarAndUser($starbarId, $userId) {
		// (see function comments for descritions of those message collections)
		// The last items will appear first. Show in reverse of importance when possible.

		$messageGroup = new Notification_MessageGroup();

		// 1. nextInGroupMessages -- those are already filtered, and already have user maps made, so let's start there
		$this->loadNextInGroupMessagesForStarbarAndUser($starbarId, $userId);

		// 2. previouslyUnscheduledMessages -- need to filter those, and add user_maps
		$previouslyUnscheduledMessages = new Notification_MessageCollection();
		$previouslyUnscheduledMessages->loadPreviouslyUnscheduledMessagesForStarbarAndUser($starbarId, $userId);
		foreach ($previouslyUnscheduledMessages as $message) {
			if ($message->validateForUser($userId)) { // user should see this message, so append to collection
				$this->addItem($message);
				// add user_map
    			$messageUserMap = new Notification_MessageUserMap();
    			$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($message->id, $userId, false);
			} else {
				$messageGroup->loadData($message->notification_message_group_id);
				$nextMessage = new Notification_Message();
				// Grab the next message in that group that the user should see
				$nextMessage->loadNextMessageInGroupForUser($message, $messageGroup, $userId);
				// If successful, append it to the collection
				if ($nextMessage->id) {
					$this->addItem($nextMessage);
					// add user_map
    				$messageUserMap = new Notification_MessageUserMap();
    				$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($nextMessage->id, $userId, false);
				}
			}
		}

		// 3. userActionMessages -- do NOT need to filter those, and they already have user maps
		$userActionMessages = new Notification_MessageCollection();
		$userActionMessages->loadUserActionMessagesForStarbarAndUser($starbarId, $userId);
		foreach ($userActionMessages as $message) {
			$this->addItem($message);
		}
	}
}
