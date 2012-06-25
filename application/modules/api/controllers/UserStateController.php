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

		// Silently fail if user_id is missing to avoid excessive thrown errors (they don't grow on trees you know)
		if (! $this->getRequest()->getParam('user_id')) return $this->_resultType(false);

		$this->_validateRequiredParameters(array('user_id'));

		$userState = new User_State();

		$userState->loadDataByUniqueFields(array('user_id' => $this->user_id));

		$install = new User_Install();
		$install->loadDataByUniqueFields(array('token'=>$this->user_key));
		
		if( $install->id && !$install->first_access_ts  && !($this->in_iframe == "true")) {
			$install->first_access_ts = new Zend_Db_Expr('now()');
			$install->save();

			if( $install->starbar_id )
				$starbar->loadData($install->starbar_id);
			else {
				$externalUser = new External_User();
				$externalUser->loadData($install->external_user_id);
				$starbar->loadData($externalUser->starbar_id);
			}

			$starbarUserMap = new Starbar_UserMap();
			$starbarUserMap->user_id = $this->user_id;
			$starbarUserMap->starbar_id = $starbar->id;
			$starbarUserMap->active = 1;
			$starbarUserMap->save();

			$isNewGamer = false;
			$gamer = Gamer::create($this->user_id, $starbar->id, $isNewGamer);
			$game = Game_Starbar::create($gamer, $this->_request, $starbar);
			if( $isNewGamer )
				$game->install();

			$userState->starbar_id = $starbar->id;
			$userState->visibility = "open";
			$userState->save();
			$userState->reload();
		} else if( !$userState->id ) {
			return $this->_resultType(false);
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
			return $this->_resultType($userState);
		} else {
			return $this->_resultType(false);
		}
	}

	public function updateAction () {
		$this->_validateRequiredParameters(array('starbar_id', 'visibility'));

		$userState = new User_State();
		$userState->loadDataByUniqueFields(array('user_id' => $this->user_id));

		$userState->starbar_id = $this->starbar_id;
		$userState->visibility = $this->visibility;

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
