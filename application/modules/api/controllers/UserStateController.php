<?php

require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Api_UserStateController extends Api_GlobalController
{
	protected function _authenticateUser($targetUserMustMatch = false, $adminOnly = false) {
		$this->user_id = $_COOKIE['user_id'];
		$this->user_key = $_COOKIE['user_key'];
		$this->auth_key = $_COOKIE['auth_key'];

		$request = $this->getRequest();
		$request->setParam('user_id', $this->user_id);
		$request->setParam('user_key', $this->user_key);
		$request->setParam('auth_key', $this->auth_key);

		$this->_validateRequiredParameters(array('user_id', 'user_key', 'auth_key'));

		parent::_authenticateUser($targetUserMustMatch, $adminOnly);
	}

	public function getAction () {
		$this->_authenticateUser();

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

		$userState->base_domain = BASE_DOMAIN;

		if ($userState->id) {
			ObjectExporter_Array::$escapeQuotes = true;
			return $this->_resultType($userState);
		} else {
			return $this->_resultType(false);
		}
	}

	public function refreshAction () {
		$this->_authenticateUser();

		$userState = new User_State();
		$userState->loadDataByUniqueFields(array('user_id' => $this->user_id));

		if ($userState->id) {
			$fields = array(
				'starbar_id',
				'visibility',
				'last_update_profile',
				'last_update_game',
			);
			ObjectExporter_Array::$escapeQuotes = true;
			return $this->_resultType(json_encode($userState->exportData($fields)));
		} else {
			return $this->_resultType(false);
		}
	}

	public function updateAction () {
		$this->_authenticateUser();
		// Uncomment next line (and delete following line) when switching starbars becomes possible
		// $this->_validateRequiredParameters(array('starbar_id', 'visibility', 'last_update_profile', 'last_update_game'));
		$this->_validateRequiredParameters(array('visibility', 'last_update_profile', 'last_update_game'));

		$request = $this->getRequest();

		$userState = new User_State();
		$userState->loadDataByUniqueFields(array('user_id' => $this->user_id));

		// Uncomment next line when switching starbars becomes possible
		// $userState->starbar_id = $this->starbar_id;
		$userState->visibility = $this->visibility;
		$userState->last_update_profile = $this->last_update_profile;
		$userState->last_update_game = $this->last_update_game;
		$userState->last_update_studies = $this->last_update_studies;

		$userState->save();

		return $this->_resultType(true);
	}

	public function updateStudiesAction () {
		$this->_authenticateUser();
		$this->_validateRequiredParameters(array('studies', 'last_update_studies'));

		$userState = new User_State();
		$userState->loadDataByUniqueFields(array('user_id' => $this->user_id));
		$userState->studies = $this->studies;
		$userState->save();

		return $this->_resultType(true);
	}

	public function updateAdTargetsAction () {
		$this->_authenticateUser();
		$this->_validateRequiredParameters(array('ad_targets'));

		$userState = new User_State();
		$userState->loadDataByUniqueFields(array('user_id' => $this->user_id));
		$userState->ad_targets = $this->ad_targets;
		$userState->save();

		return $this->_resultType(true);
	}

}
