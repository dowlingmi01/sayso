<?php

require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

/**
*
* Functions to assist with say.so testing
*
*/
class Api_TestController extends Api_GlobalController
{

	/**
	* Simulate completing a survey
	* @param None
	* @return Boolean (true) if we are on the development environments (no other checks are performed in order to get this result)
	* @return Boolean (false) if we are not on the development system
	*/
	public function completeSurveyAction() {
		if (in_array(APPLICATION_ENV, array('development', 'sandbox', 'testing', 'demo'))) {
			$this->_validateRequiredParameters(array('user_id', 'user_key', 'starbar_id', 'survey_type', 'survey_reward_category'));

			$survey = new Survey();
			$survey->type = $this->survey_type;
			$survey->reward_category = $this->survey_reward_category;
			Game_Transaction::completeSurvey($this->user_id, $this->starbar_id, $survey);

			if ($survey->type == "survey" && $survey->reward_category == "profile") {
				$profileSurvey = new Survey();
				$profileSurvey->loadProfileSurveyForStarbar($this->starbar_id);
				if ($profileSurvey->id) {
					Db_Pdo::execute("DELETE FROM survey_response WHERE survey_id = ? AND user_id = ?", $profileSurvey->id, $this->user_id);
					$surveyResponse = new Survey_Response();
					$surveyResponse->survey_id = $profileSurvey->id;
					$surveyResponse->user_id = $this->user_id;
					$surveyResponse->status = 'completed';
					$surveyResponse->save();
				}
			}

			return $this->_resultType(true);
		} else {
			return $this->_resultType(false);
		}
	}

	/**
	* Simulate receiving 5000 notes
	* @param None
	* @return Boolean (true) if we are on the development environments (no other checks are performed in order to get this result)
	* @return Boolean (false) if we are not on the development system
	*/
	public function rewardNotesAction() {
		$this->_validateRequiredParameters(array('user_id', 'starbar_id'));
		if (in_array(APPLICATION_ENV, array('development', 'sandbox', 'testing', 'demo'))) {
			Game_Transaction::run($this->user_id, Economy::getIdforStarbar($this->starbar_id), 'TEST_REWARD_NOTES');

			return $this->_resultType(true);
		} else {
			return $this->_resultType(false);
		}
	}

	/**
	* Reset surveys and polls
	* @param None
	* @return Boolean (true) if we are on the development environments (no other checks are performed in order to get this result)
	* @return Boolean (false) if we are not on the development system
	*/
	public function resetSurveysAndPollsAction() {
		if (in_array(APPLICATION_ENV, array('development', 'sandbox', 'testing', 'demo'))) {
			$this->_validateRequiredParameters(array('user_id', 'user_key', 'starbar_id'));
			Db_Pdo::execute("DELETE FROM survey_response WHERE user_id = ?", $this->user_id);
			return $this->_resultType(true);
		} else {
			return $this->_resultType(false);
		}
	}

		/**
	* Reset Facebook and Twitter External accounts and username
	* @param None
	* @return Boolean (true) if we are on the development environments (no other checks are performed in order to get this result)
	* @return Boolean (false) if we are not on the development system
	*/
	public function resetGamerAction() {
		if (in_array(APPLICATION_ENV, array('development', 'sandbox', 'testing', 'demo'))) {
			throw new Api_Exception(Api_Error::create(Api_Error::GAMING_ERROR, 'Api_TestController::resetGamerAction not implemented yet.'));
			$this->_validateRequiredParameters(array('user_id', 'user_key', 'starbar_id'));
//			$newGamer = Gamer::reset($this->user_id, $this->user_key, $this->starbar_id);
//			Game_Starbar::getInstance()->install();
			return $this->_resultType(true);
		} else {
			return $this->_resultType(false);
		}
	}

	/**
	* Reset Facebook and Twitter External accounts and username
	* @param None
	* @return Boolean (true) if we are on the development environments (no other checks are performed in order to get this result)
	* @return Boolean (false) if we are not on the development system
	*/
	public function resetExternalAction() {
		if (in_array(APPLICATION_ENV, array('development', 'sandbox', 'testing', 'demo'))) {
			$this->_validateRequiredParameters(array('user_id', 'user_key', 'starbar_id'));
			Db_Pdo::execute("DELETE FROM user_social WHERE user_id = ?", $this->user_id);
			Db_Pdo::execute("UPDATE user SET username = '' WHERE id = ?", $this->user_id);
			return $this->_resultType(true);
		} else {
			return $this->_resultType(false);
		}
	}

	/**
	* Reset Notifications and User Creation Time
	* @param None
	* @return Boolean (true) if we are on the development environments (no other checks are performed in order to get this result)
	* @return Boolean (false) if we are not on the development system
	*/
	public function resetNotificationsAction() {
		if (in_array(APPLICATION_ENV, array('development', 'sandbox', 'testing', 'demo'))) {
			$this->_validateRequiredParameters(array('user_id', 'user_key', 'starbar_id'));
			Db_Pdo::execute("DELETE FROM notification_message_user_map WHERE user_id = ?", $this->user_id);
			Db_Pdo::execute("UPDATE user SET created = now() WHERE id = ?", $this->user_id);
			return $this->_resultType(true);
		} else {
			return $this->_resultType(false);
		}
	}

	/**
	* Reset this user to 'just created, just installed'
	* @param None
	* @return Boolean (true) if we are on the development environments (no other checks are performed in order to get this result)
	* @return Boolean (false) if we are not on the development system
	*/
	public function resetAllAction() {
		if (in_array(APPLICATION_ENV, array('development', 'sandbox', 'testing', 'demo'))) {
			$this->_validateRequiredParameters(array('user_id', 'user_key', 'starbar_id'));
			Db_Pdo::execute("DELETE FROM survey_response WHERE user_id = ?", $this->user_id);
			Db_Pdo::execute("DELETE FROM notification_message_user_map WHERE user_id = ?", $this->user_id);
			Db_Pdo::execute("DELETE FROM user_social WHERE user_id = ?", $this->user_id);
			Db_Pdo::execute("DELETE FROM user_address WHERE user_id = ?", $this->user_id);
			Db_Pdo::execute("UPDATE user SET created = now(), username = '' WHERE id = ?", $this->user_id);

//			$newGamer = Gamer::reset($this->user_id, $this->user_key, $this->starbar_id);
//			Game_Starbar::getInstance()->install();

			$user = new User();
			$user->loadData($this->user_id);
			return $this->_resultType($user);
		} else {
			return $this->_resultType(false);
		}
	}

}


