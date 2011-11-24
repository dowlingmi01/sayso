<?php

require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Api_NotificationController extends Api_GlobalController
{
	public function getAllAction() {
        $this->_validateRequiredParameters(array('starbar_id', 'starbar_stowed', 'user_id', 'user_key'));

        if ($this->starbar_stowed == "true") $this->starbar_stowed = true;
        else $this->starbar_stowed = false;
        
	    $messages = new Notification_MessageCollection();
	    $messages->loadAllNotificationMessagesForStarbarAndUser($this->starbar_id, $this->starbar_stowed, $this->user_id, $this->_request);

		return $this->_resultType($messages);
	}

	// This function is called whenever a notification message is closed:
	// We need to insert or update (should always be update) a notification_message_user_map record
	// so the user doesn't see the notification again
	public function closeAction() {
        $this->_validateRequiredParameters(array('user_id', 'message_id', 'user_key'));
		$messageUserMap = new Notification_MessageUserMap();
		$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($this->message_id, $this->user_id, true);
		return $this->_resultType(true);
	}
}
