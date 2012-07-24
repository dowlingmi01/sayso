<?php


class Notification_MessageCollection extends RecordCollection
{
	// === Functions to load notifications messages from their THREE (3) sources:

	/* 1. Notifications queued for the user (e.g. for user actions) -- those are notification_message_user_map records
	*	 with an unset 'closed' timestamp ('0000-00-00 00:00:00')
	*/
	public function loadQueuedMessagesForStarbarAndUser ($starbarId, $userId) {
		$sql = "
			SELECT nm.*
			FROM notification_message nm
				INNER JOIN notification_message_group nmg
					ON nmg.id = nm.notification_message_group_id
						AND nmg.starbar_id = ?
				INNER JOIN notification_message_user_map nmum
					ON nmum.notification_message_id = nm.id
						AND nmum.user_id = ?
						AND closed = '0000-00-00 00:00:00'
			ORDER BY nm.ordinal ASC, nmum.id DESC
		";

		$data = Db_Pdo::fetchAll($sql, $starbarId, $userId);

		if ($data) {
			$this->build($data, new Notification_Message());
		}
	}

	/* 2. Notifications based on groups that are on schedules, that the user has never viewed
	*	 (i.e. no notification_message_user_map to any notification_message in a scheduled notification_message_group)
	*	 We want the first one (by ordinal) from each message group.
	*/
	public function loadPreviouslyUnscheduledMessagesForStarbarAndUser ($starbarId, $starbarStowed, $userId) {
		$starbarStowedClause = "";
		if ($starbarStowed) { // Don't get check-in notifications in stowed state
			$starbarStowedClause = " AND nm.short_name <> 'Checking in' ";
		}

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
					LEFT JOIN notification_message_user_map nmum
						ON nmum.notification_message_id = nm.id
							AND nmum.user_id = ?
							AND nmg.id = nm.notification_message_group_id
				WHERE nmum.id IS NULL
					".$starbarStowedClause."
				ORDER BY nm.ordinal ASC, nmum.id DESC
			) AS S
			GROUP BY notification_message_group_id
			ORDER BY ordinal ASC
		";

		$data = Db_Pdo::fetchAll($sql, $starbarId, $userId, $userId);

		if ($data) {
			$this->build($data, new Notification_Message());
		}
	}

	/* 3. Notifications based on groups that are on schedules, that the user has viewed and/or closed.
	*	 (i.e. notification_message_user_map exists.. if more than group's schedule minimum_interval
	*	 time has passed since the user has closed their most recent message from this group, get next
	*	 message from group if there is one, the first message in the group if there isn't a next one
	*	 and group repeats (loops), or view notification again if it was viewed but never closed)
	*/
	public function loadNextInGroupMessagesForStarbarAndUser ($starbarId, $userId) {
		$messageGroup = new Notification_MessageGroup();
		$messageUserMap = new Notification_MessageUserMap();
		$unfilteredMessages = new Notification_MessageCollection();

		$unfilteredMessages->_loadMostRecentGroupMessagesForStarbarAndUser($starbarId, $userId);
		foreach ($unfilteredMessages as $message) {
			$messageGroup->loadData($message->notification_message_group_id);
			// The most recently closed notification from that group has been closed longer than the minimum_interval between messages in that group
			$messageUserMap->loadMapForNotificationMessageAndUser($message->id, $userId);
			$closedStamp = strtotime($messageUserMap->closed);
			if ($messageGroup->minimum_interval && (time() - $closedStamp) > (int) $messageGroup->minimum_interval) {
				$nextMessage = new Notification_Message();
				// Grab the next message in that group that the user should see
				$nextMessage->loadNextMessageInGroupForUser($message, $messageGroup, $userId);

				// If successful, append it to the collection
				if ($nextMessage->id) {
					$this->addItem($nextMessage);
					// add user_map
					$messageUserMap = new Notification_MessageUserMap();
					$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($nextMessage->id, $userId, false, true);
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
					LEFT JOIN notification_message_user_map nmum
						ON nmum.notification_message_id = nm.id
							AND nmum.user_id = ?
				WHERE nm.id NOT IN
					(SELECT notification_message_id
						FROM notification_message_user_map nmum
						WHERE nmum.user_id = ?
							AND nmum.closed = '0000-00-00 00:00:00'
					)
				ORDER BY nm.ordinal ASC, nmum.id DESC
			) AS S
			GROUP BY notification_message_group_id
			ORDER BY ordinal ASC
		";

		$data = Db_Pdo::fetchAll($sql, $starbarId, $userId, $userId, $userId);

		if ($data) {
			$this->build($data, new Notification_Message());
		}
	}

	// === Now put them all together (see above), and filter and add user maps when necessary
	public function loadAllNotificationMessagesForStarbarAndUser($starbarId, $starbarStowed, $userId, $request) {
		// (see function comments for descritions of those message collections)

		$messageGroup = new Notification_MessageGroup();
		$messageUserMap = new Notification_MessageUserMap();

		// 1. queuedMessages -- those are already filtered, and already have user maps made, start here
		// Before grabbing the queued messages, process the new ones (i.e. mark them as notified, and include
		// the game/gamer objects in the request
		$this->processNewQueuedMessagesForStarbarAndUser($starbarId, $userId, $request);
		$this->loadQueuedMessagesForStarbarAndUser($starbarId, $userId);
		$queuedMessages = new Notification_MessageCollection();
		$queuedMessages->loadQueuedMessagesForStarbarAndUser($starbarId, $userId);
		foreach ($queuedMessages as $message) {
			if ($message->validateForUser($userId, $starbarId)) {
				$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($message->id, $userId, false, true); // Mark notified if needed
				$this->addItem($message);
			} else {
				// Close the message so it is no longer queued
				$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($message->id, $userId, true, false);
			}
		}

		// 2. previouslyUnscheduledMessages -- need to filter those, and add user_maps
		$previouslyUnscheduledMessages = new Notification_MessageCollection();
		$previouslyUnscheduledMessages->loadPreviouslyUnscheduledMessagesForStarbarAndUser($starbarId, $starbarStowed, $userId);
		foreach ($previouslyUnscheduledMessages as $message) {
			if ($message->validateForUser($userId, $starbarId)) { // user should see this message, so append to collection
				$this->addItem($message);
				// add user_map
				$messageUserMap = new Notification_MessageUserMap();
				$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($message->id, $userId, false, true);
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
					$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($nextMessage->id, $userId, false, true);
				}
			}
		}

		// 3. nextInGroupMessages -- those are already filtered, and already have user maps made
		$nextInGroupMessages = new Notification_MessageCollection();
		$nextInGroupMessages->loadNextInGroupMessagesForStarbarAndUser($starbarId, $userId);
		foreach ($nextInGroupMessages as $message) {
			$this->addItem($message);
		}
	}

	/* Notifications queued for the user (e.g. for user actions) sent to the user for the *first* time
	*/
	public function processNewQueuedMessagesForStarbarAndUser ($starbarId, $userId, $request) {
		$messageUserMap = new Notification_MessageUserMap();
		$game = Game_Starbar::getInstance();

		$sql = "
			SELECT nm.*
			FROM notification_message nm
				INNER JOIN notification_message_group nmg
					ON nmg.id = nm.notification_message_group_id
						AND nmg.starbar_id = ?
				INNER JOIN notification_message_user_map nmum
					ON nmum.notification_message_id = nm.id
						AND nmum.user_id = ?
						AND nmum.notified = '0000-00-00 00:00:00'
						AND nmum.closed = '0000-00-00 00:00:00'
			ORDER BY nm.ordinal ASC, nmum.id DESC
		";

		$data = Db_Pdo::fetchAll($sql, $starbarId, $userId);

		$loadGame = false;

		if ($data) {
			$newMessages = new Notification_MessageCollection();
			$newMessages->build($data, new Notification_Message());

			foreach ($newMessages as $message) {
				switch ($message->short_name) {
					case 'Update Game':
						$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($message->id, $userId, true);
						$loadGame = true;
						break;
					case 'Checking in':
						$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($message->id, $userId);
						$game->checkin();
						break;
					case 'FB Account Connected':
					case 'TW Account Connected':
						$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($message->id, $userId);
						$loadGame = true;
						break;
					default:
						$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($message->id, $userId);
				}
			}
		}

		if ($loadGame) {
			$game->loadGamerProfile();
			$request->setParam(Api_AbstractController::GAME, $game);
		}
	}

}
