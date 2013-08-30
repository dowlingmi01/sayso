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

		//logic
		$userId = $request->getUserId();

		$user = new User();
		$user->loadData($userId);
		$user->getUserSocials();
		$userExportData = $user->exportData();
		$userPropertiesData = $user->exportProperties();

		//hack to convert _user_socials to be usable
		$userSocials = $response->getRecordsFromCollection($userPropertiesData["_user_socials"]);
		if (is_array($userSocials))
		{
			$formattedUserSocials = array();
			foreach ($userSocials as $key => $value)
			{
				$formattedUserSocials[$value["provider"]] = $value;
			}
		}
		$userPropertiesData["_user_socials"] = $formattedUserSocials;

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

		//logic
		$userId = $request->getUserId();
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

		//logic
		$userId = $request->getUserId();

		$userState = new User_State();
		$userState->loadDataByUniqueFields(array('user_id' => $userId));

		if (isset($request->submitted_parameters->state_data))
		if ($stateData = $request->getParam("state_data"))
		{
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

		//logic
		$userId				= $request->getUserId();
		$network			= strtoupper($request->getParam("network"));
		$starbarId			= $request->getParam("starbar_id");
		$oauth				= $request->getParam("oauth");

		switch($network)
		{
			case "FB" :
				if (!$this->_hasSocial($userId, "facebook"))
					$connected = User_Social::connectFacebook($userId, $starbarId);
				else
					throw new Exception("Already connected.");
				break;
			case "TW" :
				if (!$this->_hasSocial($userId, "twitter"))
				{
					if (!property_exists($request->submitted_parameters, "oauth"))
						throw new Exception("Missing Twitter oauth credentials.");
					$connected = User_Social::connectTwitter($userId, $starbarId, $oauth);
				} else
					throw new Exception("Already connected.");
				break;
			default :
				throw new Exception('Invalid network.');
		}

		if ($connected === TRUE) {
			$response->setResultVariable("success", TRUE);

			//update user as commondata.user
			//set params for sending to updateStatus endpoint
			$response->addCommonData("user", array("class" => get_class(), "request_name" => $this->request_name));
		} elseif (is_string($connected)) {
			$response->setResultVariable("success", FALSE);
			$response->setResultVariable("login_url", $connected);
		} elseif ($connected instanceof Exception) {
			throw new Exception($connected->getMessage());
		} else {
			throw new Exception("Unknown error.");
		}
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
	public function getTwitterOauthToken(Ssmart_EndpointRequest $request)
	{
		$response = new Ssmart_EndpointResponse($request);

		//logic
		$callbackUrl = $request->getParam("callback_url");

		$token = User_Social::getTwiterOauthToken($callbackUrl);
		if ($token)
		{
			$response->setResultVariable("success", TRUE);
			$response->setResultVariable("oauth_token", $token["token"]);
			$response->setResultVariable("oauth_token_secret", $token["token_secret"]);
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

	private function _hasSocial($userId, $network)
	{
		//check for existing social connection
		$social = new User_Social();
		return $social->loadByUserIdAndProvider($userId, $network);
	}
}