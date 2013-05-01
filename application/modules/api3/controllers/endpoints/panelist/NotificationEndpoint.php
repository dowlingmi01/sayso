<?php
/**
 * <p>Notification endpoiints</p>
 *
 * @package Api3
 * @subpackage endpoint
 */
class Api3_NotificationEndpoint extends Api3_GlobalController
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
		$validators = array(
				"starbar_id"			=> "int_required_notEmpty",
				"starbar_stowed"		=> "required_allowEmpty"
			);
		$filters = array(
				"starbar_stowed"		=> "bool"
			);

		$response = new Api3_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		$starbarId					= $request->validParameters["starbar_id"];
		$userId					= $request->auth->userData->user_id;
		$starbarStowed				= $request->validParameters["starbar_stowed"];

		$messages = new Notification_MessageCollection();
		$messages->loadAllNotificationMessagesForStarbarAndUser($starbarId, $starbarStowed, $userId, NULL);

		$response->addRecordsFromCollection($messages);

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
	 *<p> NOTES: using the existing updateOrInsertMapForNotificationMessageAndUser
	 *	function does not return the id of the field updated
	 *	this is what should be the key identifier, but since it's not returned, we just
	 *	use the notification_message_group.id</p>
	 *
	 * @param Api3_EndpointRequest $request
	 * @return Api3_EndpointResponse
	 */
	public function updateStatus(Api3_EndpointRequest $request)
	{
		$validators = array(
				"message_id"			=> "int_required_notEmpty",
				"mark_closed"			=> "required_allowEmpty",
				"mark_notified"			=> "required_allowEmpty"
			);
		$filters = array(
				"mark_closed"			=> "bool",
				"mark_notified"			=> "bool"
			);

		$response = new Api3_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		$userId					= $request->auth->userData->user_id;
		$messageId				= $request->validParameters["message_id"];
		$markClosed				= $request->validParameters["mark_closed"];
		$markNotified				= $request->validParameters["mark_notified"];

		$messageUserMap = new Notification_MessageUserMap();
		$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($messageId, $userId, $markClosed, $markNotified);

		$response->setResultVariable("success", TRUE);

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
		$validators = array(
				"short_name"			=> "alpha_required_allowEmpty",
				"starbar_id"			=> "int_required_notEmpty",
				"mark_notified"			=> "required_allowEmpty",
				"mark_closed"			=> "required_allowEmpty"
			);
		$filters = array("mark_closed" => "bool", "mark_notified" => "bool");

		$response = new Api3_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		$shortName				= $request->validParameters["short_name"];
		$starbarId					= $request->validParameters["starbar_id"];
		$markNotified				= $request->validParameters["mark_notified"];
		$markClosed				= $request->validParameters["mark_closed"];

		$message = new Notification_Message();
		$message->loadByShortNameAndStarbarId($shortName, $starbarId);

		//throw api error if no $message->id
		if (!$message->id)
		{
			$response->setResponseError(array("code" => "message_group_id_lookup_failed", "message" => "Failed to find a message group with short name = " . $request->validParameters["short_name"]));
			return $response;
		}

		//set params for sending to updateStatus endpoint
		$otherEndpointParams = array("message_id" => $message->id, "mark_closed" => $markClosed, "mark_notified" => $markNotified);

		$response->addFromOtherEndpoint("updateStatus", get_class(), $otherEndpointParams, $this->request_name);

		return $response;
	}
}