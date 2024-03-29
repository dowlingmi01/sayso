<?php


class Notification_Message extends Record
{
	protected $_tableName = 'notification_message';

	public function loadNextMessageInGroupForUser($message, $messageGroup, $userId, $stopId = null) {
		if (!$stopId) $stopId = $message->id; // first run through, set $stopId to our starting point to avoid looping forever

		// Select the next message in the group
		$sql = "
			SELECT nm.*
			FROM notification_message nm
				INNER JOIN notification_message_group nmg
					ON nmg.id = nm.notification_message_group_id
					AND nmg.id = ?
			WHERE nm.ordinal > ?
			ORDER BY nm.ordinal ASC
			LIMIT 1
		";
		$data = Db_Pdo::fetch($sql, $messageGroup->id, $message->ordinal);

		if ($data) {
			$this->build($data);

		// No next message... if the group repeats, loop around it and get the first message
		} elseif ($messageGroup->repeats) {
			// Select the first message in the group
			$sql = "
				SELECT nm.*
				FROM notification_message nm
					INNER JOIN notification_message_group nmg
						ON nmg.id = nm.notification_message_group_id
						AND nmg.id = ?
				ORDER BY nm.ordinal ASC
				LIMIT 1
			";
			$data = Db_Pdo::fetch($sql, $messageGroup->id);

			if ($data) {
				$this->build($data);
			}
		}

		if ($data) {
			// Should the user see this notification?
			$validMessage = $this->validateForUser($userId, $messageGroup->starbar_id);
		}

		if ($data // We got a result
			&& !$validMessage // The user shouldn't see it
			&& $this->id != $stopId // We haven't looped around the group yet, so...
		) {
			// ... grab the next message in the group!
			return $this->loadNextMessageInGroupForUser($this, $messageGroup, $userId, $stopId);

		} elseif ($this->id && $validMessage) { // We got a result that the user should see
			return true;
		} else { // No more messages in this group for this user
			$this->id = 0;
			return false;
		}

		// If we haven't built $this by now, there are no next messages in the group for this user. All done!
	}

	public function validateForUser($userId, $starbarId) {
		// Validate based on the message's validate field
		switch ($this->validate) {
			case 'Facebook Connect':
				$userSocial = new User_Social();
				if ($userSocial->loadByUserIdAndProvider($userId, 'facebook')) return false;
				break;

			case 'Twitter Connect':
				$userSocial = new User_Social();
				if ($userSocial->loadByUserIdAndProvider($userId, 'twitter')) return false;
				break;

			case 'Take Survey':
				if (Survey_Response::checkIfUserHasCompletedSurvey($userId, $this->survey_id)) return false;
				break;

			case 'Taken Survey':
				if (! Survey_Response::checkIfUserHasCompletedSurvey($userId, $this->survey_id)) return false;
				break;

			case 'New Quizzes':
				if (! Survey_ResponseCollection::checkIfUserHasSurveys($userId, $starbarId, 'quiz', 'new')) return false;
				break;

			case 'New Trailers':
				if (! Survey_ResponseCollection::checkIfUserHasSurveys($userId, $starbarId, 'trailer', 'new')) return false;
				break;

			case 'New Missions':
				if (! Survey_ResponseCollection::checkIfUserHasSurveys($userId, $starbarId, 'mission', 'new')) return false;
				break;
		}

		return true;
	}

	public function loadByShortNameAndStarbarId($shortName, $starbarId) {
		$sql = "
			SELECT nm.*
			FROM notification_message nm
				INNER JOIN notification_message_group nmg
					ON nmg.id = nm.notification_message_group_id
					AND nmg.starbar_id = ?
			WHERE nm.short_name LIKE ?
			ORDER BY nm.ordinal ASC
			LIMIT 1
		";
		$data = Db_Pdo::fetch($sql, $starbarId, $shortName);

		if ($data) {
			$this->build($data);
		}
	}
}
