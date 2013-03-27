<?php
/**
 * <p>Notification endpoiints</p>
 *
 * @package Api3
 * @subpackage endpoint
 */
class Api3_NotificationController extends Api3_GlobalController
{
	/**
	 * Returns all users notifications'.
	 *
	 *<p><b>Required params:</b>
	 *	starbar_id
	 * <b>optional params:</b>
	 *	starbar_stowed
	 *	results_per_page
	 *	page_number</p>
	 *
	 * @param Api3_EndpointRequest $request
	 * @return Api3_EndpointResponse
	 */
	public  function getUserNotifications(Api3_EndpointRequest $request)
	{
		$response = new Api3_EndpointResponse($request);

		$request->addValidators(array("starbar_id" => "int_required_notEmpty", "starbar_stowed" => "required_allowEmpty"));
		$request->addFilters(array("starbar_stowed" => "bool"));

		$request->preProcess();

		if ($request->hasErrors())
			return $response->addError();

		//logic
		$messages = new Notification_MessageCollection();
		$messages->loadAllNotificationMessagesForStarbarAndUser($request->validParameters["starbar_id"], $request->validParameters["starbar_stowed"], $request->auth->user_id, NULL);

		$data = $messages->getArray();

		//format the result set and add the _key_idntifier value to the result set so the api can fomrat the response correctly
		$formattedData = array();
		foreach($data as $key => $value)
		{
			$formattedData[$key] = $value->getData();
		}

		//count logic
		$count = count($formattedData);

		$response->addRecordsFromArray($formattedData);
		$response->addPagination($count);

		if ($response->hasErrors())
			return $response->addError();

		return $response;
	}

	/**
	 * Updates the notification_message_user_map table
	 * and adds date to the closed and modified columns
	 * based on a notification_message_id and user_id
	 *
	 * <p><b>required params :</b>
	 *	message_id
	 *	mark_closed
	 *	mark_notified</p>
	 *<p> NOTES: using the existing function does not return the id of the field updated
	 *	this is what should be the key identifier, but since it's not returned, we just
	 *	use the notification_message_group.id</p>
	 *
	 * @param Api3_EndpointRequest $request
	 * @return Api3_EndpointResponse
	 */
	public function updateStatus(Api3_EndpointRequest $request)
	{
		$response = new Api3_EndpointResponse($request);

		$request->addValidators(array("message_id" => "int_required_notEmpty", "mark_closed" => "required_allowEmpty", "mark_notified" => "required_allowEmpty"));
		$request->addFilters(array("mark_closed" => "bool", "mark_notified" => "bool"));

		$request->preProcess();

		if ($request->hasErrors())
			return $response->addError();

		//logic
		$messageUserMap = new Notification_MessageUserMap();
		$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($request->validParameters["message_id"], $request->auth->user_id, $request->validParameters["mark_closed"], $request->validParameters["mark_notified"]);

		$response->addContext("success", TRUE);

		return $response;
	}

	/**
	 * Update or insert new notification_message_user_map record,
	 *	input a notification_message.short_name and starbar id to
	 *	 resolve the notification_message_group.id,
	 *	then pass that to the updateStatus endpoint
	 *
	 * <p><b>required params: </b>
	 *	short_name
	 *	starbar_id
	 * <b>optional params :</b>
	 *	mark_closed
	 *	mark_notified</p>
	 * <p>NOTES: using the existing function does not return the id of the field updated
	 *	this is what should be the key identifier, but since it's not returned, we just
	 *	use the notification_message_group.id</p>
	 *
	 * @param Api3_EndpointRequest $request
	 * @return Api3_EndpointResponse
	 */
	public function saveStatusByShortNameAndStarbar(Api3_EndpointRequest $request)
	{
		$response = new Api3_EndpointResponse($request);

		$request->addValidators(array("short_name" => "alpha_required_allowEmpty", "starbar_id" => "int_required_notEmpty", "mark_notified" => "required_allowEmpty", "mark_closed" => "required_allowEmpty"));
		$request->addFilters(array("mark_closed" => "bool", "mark_notified" => "bool"));

		$request->preProcess();

		if ($request->hasErrors())
			return $response->addError();

		//logic
		$message = new Notification_Message();
		$message->loadByShortNameAndStarbarId($request->validParameters["short_name"],$request->validParameters["starbar_id"]);

		//throw api error if no $message->id
		if (!$message->id)
			return $response->addError(array("code" => "message_group_id_lookup_failed", "message" => "Failed to find a message group with short name = " . $request->validParameters["short_name"]));

		//set params for sending to updateStatus endpoint
		$updateStatusParams = array("message_id" => $message->id, "mark_closed" => $request->validParameters["mark_closed"], "mark_notified" => $request->validParameters["mark_notified"]);

		$response->addFromOtherEndpoint("updateStatus", get_class(), $updateStatusParams, $this->request_name);

		if ($response->hasErrors())
			return $response->addError();

		return $response;
	}
}