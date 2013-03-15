<?php

class Api3_NotificationController extends Api3_GlobalController
{
	/**returns all users with optional pagination
	 *
	 * this is more an easy test case to prove the concept
	 * than a usable example
	 *
	 * @param \stdClass $params
	 * @return int|\stdClass
	 */
	public  function getUserNotifications($params)
	{
		//overload $_key_identifier
		$this->_key_identifier = "notification_message_id";

		//define custom validators and filters
		$additionalValidators = array(
							"starbar_id" => array(
												new Zend_Validate_Int(),
												"allowEmpty" => FALSE
											),
							"request_name" => array(
												new Zend_Validate_Alpha(),
												"allowEmpty" => TRUE
											),
							"starbar_stowed" => array(
												new Zend_Validate_Alpha(),
												"allowEmpty" => FALSE
											)
						);

		$preProcess = $this->_preProcess($params, $additionalValidators);

		//validate
		if (!$preProcess)
			return _prepareError(get_class() . "_failed", "Failed to get valid params from validator.");
		//check for validation errors
		if (isset($preProcess->error))
			return $preProcess;

		//logic
		if ($preProcess["starbar_stowed"] == "true") $preProcess["starbar_stowed"] = TRUE;
		else $preProcess["starbar_stowed"] = FALSE;

		$messages = new Notification_MessageCollection();
		$messages->loadAllNotificationMessagesForStarbarAndUser($preProcess["starbar_id"], $preProcess["starbar_stowed"], $this->auth->user_id, NULL);

		$data = $messages->getArray();

		//format the result set and add the _key_idntifier value to the result set so the api can fomrat the response correctly
		foreach($data as $key => $value)
		{
			$this->_results->$key = $value->getData();
			$key_identifier = array("notification_message_id" => $key);
			$this->_results->$key = array_merge($this->_results->$key, $key_identifier);
		}

		//count logic
		$count = $this->_countResults($this->_results);

		//processes the logic and adds the pagination stuff
		$resultSet = $this->_prepareResponse($this->_results, $preProcess, $count);

		return $resultSet;
	}

	/**
	 *
	 * @param type $params
	 */
	public function updateStatus($params)
	{
		//overload $_key_identifier
		$this->_key_identifier = "message_id";

		//define custom validators and filters
		$additionalValidators = array(
							"message_id" => array(
												new Zend_Validate_Int(),
												"allowEmpty" => FALSE
											)
						);

		$preProcess = $this->_preProcess($params, $additionalValidators);

		//validate
		if (!$preProcess)
			return _prepareError(get_class() . "_failed", "Failed to get valid params from validator.");
		//check for validation errors
		if (isset($preProcess->error))
			return $preProcess;
		//logic
		$messageUserMap = new Notification_MessageUserMap();
		$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($preProcess["message_id"], $this->auth->user_id, TRUE);

		$this->_results->{$preProcess[$this->_key_identifier]} = array($this->_key_identifier => $preProcess["message_id"], "updated" => TRUE);

		//processes the logic and adds the pagination stuff
		$resultSet = $this->_prepareResponse($this->_results, $preProcess);

		return $resultSet;
	}
}