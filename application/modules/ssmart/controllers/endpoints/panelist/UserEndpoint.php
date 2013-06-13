<?php
/**
 * <p>User endpoiints</p>
 *
 * @package Ssmart
 * @subpackage endpoint
 */
class Ssmart_Panelist_UserEndpoint extends Ssmart_GlobalController
{
	/**
	 * Gets the user data for the current user.
	 *
	 * @param Ssmart_EndpointRequest $request
	 * @return \Ssmart_EndpointResponse
	 */
	public function getUser(Ssmart_EndpointRequest $request)
	{
		$response = new Ssmart_EndpointResponse($request);

		if ($response->hasErrors())
			return $response;

		//logic
		$userId = $request->auth->user_data->user_id;

		$user = new User();
		$user->loadData($userId);
		$userExportData = $user->exportData();
		$userPropertiesData = $user->exportProperties();
		$userData = array_merge($userExportData, $userPropertiesData);

		$cleanUser = array($userId => $this->_cleanUserResponse($userData));

		$response->addRecordsFromArray($cleanUser);

		return $response;
	}

	/**
	 * Gets the user state.
	 *
	 * @param Ssmart_EndpointRequest $request
	 * @return \Ssmart_EndpointResponse
	 */
	public function getState(Ssmart_EndpointRequest $request)
	{
		$response = new Ssmart_EndpointResponse($request);

		if ($response->hasErrors())
			return $response;

		//logic
		$userId = $request->auth->user_data->user_id;

		$userState = new User_State();
		$userState->loadDataByUniqueFields(array('user_id' => $userId));
		$userStateData = $userState->getData();

		$response->setResultVariables($userStateData);

		return $response;
	}

	/**
	 * Updates the User_State
	 *
	 * @param Ssmart_EndpointRequest $request
	 * @return \Ssmart_EndpointResponse
	 * @throws Exception
	 */
	public function updateState(Ssmart_EndpointRequest $request)
	{
		$response = new Ssmart_EndpointResponse($request);

		if ($response->hasErrors())
			return $response;

		//logic
		$userId = $request->auth->user_data->user_id;

		$userState = new User_State();
		$userState->loadDataByUniqueFields(array('user_id' => $userId));

		if (isset($request->submitted_parameters->state_data))
		{
			$stateData = $request->submitted_parameters->state_data;
			if (!is_object($stateData) && !is_array($stateData))
				throw new Exception('Invalid $stateData.');
			//TODO: valdate visibility enum
			foreach ($stateData as $key => $value)
			{
				$userState->{$key} = $value;
			}
		} else
			throw new Exception('Missing state data.');

		$updateStatus = $userState->save() ? TRUE : FALSE;

		$response->setResultVariable("success", $updateStatus);

		return $response;
	}

	/**
	 * Connects a user to a social network.
	 *
	 * <p><b>required params: </b>
	 *	network
	 *	starbar_id</p>
	 *
	 * @param Ssmart_EndpointRequest $request
	 * @return \Ssmart_EndpointResponse
	 * @throws Exception
	 */
	public function connectSocialNetwork(Ssmart_EndpointRequest $request)
	{
		$validators = array(
				"network"			=> "alpha_required_notEmpty",
				"starbar_id"		=> "int_required_notEmpty"
			);
		$filters = array();

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		$userId			= $request->auth->user_data->user_id;
		$network			= strtoupper($request->valid_parameters["network"]);
		$starbarId			= $request->valid_parameters["starbar_id"];

		switch($network)
		{
			case "FB" :
				User_Social::connectFacebook($userId, $starbarId);
				break;
			case "TW" :
				if (!$request->submitted_parameters->oauth)
					throw new Exception("Missing Twitter oauth credentials.");
				User_Social::connectTwitter($userId, $starbarId, $oauth);
				break;
			default :
				throw new Exception('Invalid network.');
		}

		$response->setResultVariable("success", TRUE);

		return $response;
	}

	/**
	 * Gets the oauth token for a Twitter user.
	 *
	 * <p><b>optional params: </b>
	 *	callback_url</p>
	 *
	 * @param Ssmart_EndpointRequest $request
	 * @return \Ssmart_EndpointResponse
	 */
	public function getTwiterOauthToken(Ssmart_EndpointRequest $request)
	{
		$response = new Ssmart_EndpointResponse($request);

		if ($response->hasErrors())
			return $response;

		//logic
		$callbackUrl		= $$request->submitted_parameters->callback_url;

		$token = User_Social::getTwiterOauthToken($callbackUrl);
		if (token)
		{
			$response->setResultVariable("success", TRUE);
			$response->setResultVariable("token", $token);
		} else
			$response->setResultVariable("success", FALSE);

		return $response;

	}

//////////Helper functions/////////////

	/**
	 * Removes unecessary fields and changes field names
	 * on the user object.
	 *
	 * @param type $userData
	 * @return type
	 */
	private function _cleanUserResponse($userData)
	{
		$userFieldsToRemove = array(
			"first_name"			=> "",
			"last_name"			=> "",
			"birthdate"				=> "",
			"timezone"				=> "",
			"_preferences"			=> "",
			"_survey_types"			=> ""
		);

		$userFieldsToRename = array(
			"username"			=> "public_name",
			"_email"				=> "email",
			"_user_socials"			=> "user_socials"
		);

		$newUser = array_diff_key($userData, $userFieldsToRemove);

		foreach ($userFieldsToRename as $key => $value)
		{
			if (array_key_exists($key, $newUser))
			{
				$newUser[$value] = $newUser[$key];
				unset($newUser[$key]);
			}
		}

		return $newUser;
	}
}