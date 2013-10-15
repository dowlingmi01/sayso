<?php
/**
 * <p>Notification endpoiints</p>
 *
 * @package Ssmart
 * @subpackage endpoint
 */
class Ssmart_Panelist_NotificationEndpoint extends Ssmart_GlobalController
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
	 * @param Ssmart_EndpointRequest $request
	 * @return Ssmart_EndpointResponse
	 * @todo add pagination
	 */
	public  function getUserNotifications(Ssmart_EndpointRequest $request)
	{
		$validators = array(
				"starbar_id"			=> "int_required_notEmpty",
				"starbar_stowed"		=> "required_allowEmpty"
			);
		$filters = array(
				"starbar_stowed"		=> "bool"
			);

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		//logic
		$starbarId					= $request->getParam("starbar_id");
		$userId						= $request->getUserId();
		$starbarStowed				= $request->getParam("starbar_stowed");

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
	 * @param Ssmart_EndpointRequest $request
	 * @return Ssmart_EndpointResponse
	 */
	public function updateStatus(Ssmart_EndpointRequest $request)
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

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		//logic
		$userId					= $request->getUserId();
		$messageId				= $request->getParam("message_id");
		$markClosed				= $request->getParam("mark_closed");
		$markNotified			= $request->getParam("mark_notified");

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
	 * @param Ssmart_EndpointRequest $request
	 * @return Ssmart_EndpointResponse
	 * @throws Exception
	 */
	public function saveStatusByShortNameAndStarbar(Ssmart_EndpointRequest $request)
	{
		$validators = array(
				"short_name"			=> "alpha_required_allowEmpty",
				"starbar_id"			=> "int_required_notEmpty",
				"mark_notified"			=> "required_allowEmpty",
				"mark_closed"			=> "required_allowEmpty"
			);
		$filters = array("mark_closed" => "bool", "mark_notified" => "bool");

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		//logic
		$shortName				= $request->getParam("short_name");
		$starbarId				= $request->getParam("starbar_id");
		$markNotified			= $request->getParam("mark_notified");
		$markClosed				= $request->getParam("mark_closed");
		$shortName				= $request->getParam("short_name");

		$message = new Notification_Message();
		$message->loadByShortNameAndStarbarId($shortName, $starbarId);

		//throw api error if no $message->id
		if (!$message->id)
		{
			throw new Exception("Failed to find a message group with short name = " . $shortName);
		}

		//set params for sending to updateStatus endpoint
		$otherEndpointParams = array("message_id" => $message->id, "mark_closed" => $markClosed, "mark_notified" => $markNotified);

		$response->addFromOtherEndpoint("updateStatus", get_class(), $otherEndpointParams, $this->request_name);

		return $response;
	}
}