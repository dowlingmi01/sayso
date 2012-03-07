<?php

require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Api_UserStateController extends Api_GlobalController
{
	public function getAction () {
		$userState = new User_State();

		/*
		@todo add caching?
		$cache = Api_Cache::getInstance('User_State_'.$this->user_id, Api_Cache::LIFETIME_HOUR);
		if ($cache->test()) {
			$userState = $cache->load();
		} else {
			$userState->loadDataByUniqueFields(array('user_id' => $this->user_id));
			$cache->save($userState); // <-- note 'studies' tag used for cleaning
		}*/
		$userState->loadDataByUniqueFields(array('user_id' => $this->user_id));
		
		if (!$userState->id) { // This must be the first install for this user
			$install = new External_UserInstall();
			$install->loadDataByUniqueFields(array('token'=>$this->user_key));
			$install->first_access_ts = new Zend_Db_Expr('now()');
			$install->save();

			$externalUser = new External_User();
			$externalUser->loadData($install->external_user_id);

			$starbarUserMap = new Starbar_UserMap();
			$starbarUserMap->user_id = $this->user_id;
			$starbarUserMap->starbar_id = $externalUser->starbar_id;
			$starbarUserMap->active = 1;
			$starbarUserMap->save();

			$gamer = Gamer::create($this->user_id, $externalUser->starbar_id);
			$starbar = new Starbar();
			$starbar->loadData($externalUser->starbar_id);
			$game = Game_Starbar::create($gamer, $this->_request, $starbar);
			$game->install();
			
			$userState->starbar_id = $externalUser->starbar_id;
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
