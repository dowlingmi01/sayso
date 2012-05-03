<?php

require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Api_UserStateController extends Api_GlobalController
{
	public function getAction () {
		$starbar = new Starbar();
		$newToken = false;
		
		if ($this->client_user_logged_in) {
			$this->_validateRequiredParameters(array('client_name', 'client_uuid', 'client_uuid_type'));
			
			$externalUser = new External_User();
			$starbar->loadDataByUniqueFields( array('short_name' => $this->client_name));
			$externalUser->starbar_id = $starbar->id;
			$externalUser->uuid = $this->client_uuid;
			$externalUser->uuid_type = $this->client_uuid_type;
			$externalUser->email = $this->client_email;
			$externalUser->loadOrCreate();
			
			$user = $externalUser->getUser();
			
			if( $externalUser->user_id != $this->user_id ) {
				$userKey = new User_Key();
				$userKey->user_id = $user->getId();
				$userKey->token = User_Key::getRandomToken();
				$userKey->origin = User_Key::ORIGIN_USER_STATE;
				$userKey->save();
				
				header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTR STP IND DEM"');
				setcookie('user_key', $userKey->token, time()+(86400*365), '/', null, null, true);
				
				$this->user_id = $externalUser->user_id;
				$this->user_key = $userKey->token;
				$newToken = true;
			}
		}

		$this->_validateRequiredParameters(array('user_id'));
		
		$userState = new User_State();

		$userState->loadDataByUniqueFields(array('user_id' => $this->user_id));
				
		if (!$userState->id) { // This must be the first install for this user
			if( !$newToken ) {
				$install = new User_Install();
				$install->loadDataByUniqueFields(array('token'=>$this->user_key));
				$install->first_access_ts = new Zend_Db_Expr('now()');
				$install->save();

				if( $install->starbar_id )
					$starbar->loadData($install->starbar_id);
				else {
					$externalUser = new External_User();
					$externalUser->loadData($install->external_user_id);
					$starbar->loadData($externalUser->starbar_id);
				}
			}

			$starbarUserMap = new Starbar_UserMap();
			$starbarUserMap->user_id = $this->user_id;
			$starbarUserMap->starbar_id = $starbar->id;
			$starbarUserMap->active = 1;
			$starbarUserMap->save();

			$gamer = Gamer::create($this->user_id, $starbar->id);
			$game = Game_Starbar::create($gamer, $this->_request, $starbar);
			$game->install();
			
			$userState->starbar_id = $starbar->id;
			$userState->auth_key = $starbar->auth_key;
			$userState->visibility = "open";
			$userState->save();
			$userState->reload();
		}		

		$userState->base_domain = BASE_DOMAIN;
		$userState->user_key = $this->user_key;

		return $this->_resultType($userState);
	}

	public function refreshAction () {
		$userState = new User_State();
		$userState->loadDataByUniqueFields(array('user_id' => $this->user_id));

		if ($userState->id) {
			$fields = array(
				'starbar_id',
				'visibility',
				'last_update_profile',
				'last_update_game',
			);
			return $this->_resultType(json_encode($userState->exportData($fields)));
		} else {
			return $this->_resultType(false);
		}
	}

	public function updateAction () {
		// Uncomment next line (and delete following line) when switching starbars becomes possible
		// $this->_validateRequiredParameters(array('starbar_id', 'visibility', 'last_update_profile', 'last_update_game'));
		$this->_validateRequiredParameters(array('visibility', 'last_update_profile', 'last_update_game'));

		$userState = new User_State();
		$userState->loadDataByUniqueFields(array('user_id' => $this->user_id));

		// Uncomment next line when switching starbars becomes possible
		// $userState->starbar_id = $this->starbar_id;
		$userState->visibility = $this->visibility;
		$userState->last_update_profile = $this->last_update_profile;
		$userState->last_update_game = $this->last_update_game;

		$userState->save();

		return $this->_resultType(true);
	}

	public function updateStudiesAction () {
		$this->_validateRequiredParameters(array('studies', 'last_update_studies'));

		$userState = new User_State();
		$userState->loadDataByUniqueFields(array('user_id' => $this->user_id));
		$userState->studies = $this->studies;
		$userState->last_update_studies = $this->last_update_studies;
		$userState->save();

		return $this->_resultType(true);
	}

	public function updateAdTargetsAction () {
		$this->_validateRequiredParameters(array('ad_targets'));

		$userState = new User_State();
		$userState->loadDataByUniqueFields(array('user_id' => $this->user_id));
		$userState->ad_targets = $this->ad_targets;
		$userState->save();

		return $this->_resultType(true);
	}

}
