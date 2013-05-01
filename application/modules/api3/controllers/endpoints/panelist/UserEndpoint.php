<?php
/**
 * <p>User endpoiints</p>
 *
 * @package Api3
 * @subpackage endpoint
 */
class Api3_UserEndpoint extends Api3_GlobalController
{
	/**
	 * Gets the user data for the current user.
	 *
	 * @param Api3_EndpointRequest $request
	 * @return \Api3_EndpointResponse
	 */
	public function getUser(Api3_EndpointRequest $request)
	{
		$response = new Api3_EndpointResponse($request);

		if ($response->hasErrors())
			return $response;

		//logic
		$userId = $request->auth->userData->user_id;

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
	 * @param Api3_EndpointRequest $request
	 * @return \Api3_EndpointResponse
	 */
	public function getState(Api3_EndpointRequest $request)
	{
		$response = new Api3_EndpointResponse($request);

		if ($response->hasErrors())
			return $response;

		//logic
		$userId = $request->auth->userData->user_id;

		$userState = new User_State();
		$userState->loadDataByUniqueFields(array('user_id' => $userId));
		$userStateData = $userState->getData();

		$response->addRecordsFromArray($userStateData);

		return $response;
	}

	/**
	 * Updates the User_State
	 *
	 * @param Api3_EndpointRequest $request
	 * @return \Api3_EndpointResponse
	 * @throws Exception
	 */
	public function updateState(Api3_EndpointRequest $request)
	{
		$response = new Api3_EndpointResponse($request);

		if ($response->hasErrors())
			return $response;

		//logic
		$userId = $request->auth->userData->user_id;

		$userState = new User_State();
		$userState->loadDataByUniqueFields(array('user_id' => $userId));

		if (isset($request->submittedParameters->state_data))
		{
			$stateData = $request->submittedParameters->state_data;
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
	 * @param Api3_EndpointRequest $request
	 * @return \Api3_EndpointResponse
	 * @throws Exception
	 */
	public function connectSocialNetwork(Api3_EndpointRequest $request)
	{
		$validators = array(
				"network"			=> "alpha_required_notEmpty",
				"starbar_id"		=> "int_required_notEmpty"
			);
		$filters = array();

		$response = new Api3_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		$userId			= $request->auth->userData->user_id;
		$network			= $request->validParameters["network"];
		$starbarId			= $request->validParameters["starbar_id"];

		switch($network)
		{
			case "facebook" :
				User_Social::connectFacebook($userId, $starbarId);
				break;
			case "twitter" :
				User_Social::connectTwitter($userId, $starbarId);
				break;
			default :
				throw new Exception('Invalid network.');
		}

		$response->setResultVariable("success", TRUE);

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