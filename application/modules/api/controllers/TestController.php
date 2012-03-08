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
    * Simulate completing initial survey
    * @param None
    * @return Boolean (true) if we are on the development environments (no other checks are performed in order to get this result)
    * @return Boolean (false) if we are not on the development system
    */
    public function completeSurveyAction() {
        if (in_array(APPLICATION_ENV, array('development', 'sandbox', 'testing', 'demo'))) {
            $this->_validateRequiredParameters(array('user_id', 'user_key', 'starbar_id', 'survey_type', 'survey_premium'));

            $survey = new Survey();
            $survey->type = $this->survey_type;
            $survey->premium = ( $this->survey_premium == "true" ? true : false );
            Game_Starbar::getInstance()->completeSurvey($survey);

            if ($survey->type == 'survey' && $survey->premium) {
                Db_Pdo::execute("DELETE FROM survey_user_map WHERE survey_id = 1 AND user_id = ?", $this->user_id);
                $surveyUserMap = new Survey_UserMap();
                $surveyUserMap->survey_id = 1;
                $surveyUserMap->user_id = $this->user_id;
                $surveyUserMap->status = 'completed';
                $surveyUserMap->save();
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
        if (in_array(APPLICATION_ENV, array('development', 'sandbox', 'testing', 'demo'))) {
            Game_Starbar::getInstance()->testRewardNotes($survey);

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
            Db_Pdo::execute("DELETE FROM survey_user_map WHERE user_id = ?", $this->user_id);
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
            $this->_validateRequiredParameters(array('user_id', 'user_key', 'starbar_id'));
            $newGamer = Gamer::reset($this->user_id, $this->user_key, $this->starbar_id);
            Game_Starbar::getInstance()->install();
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
            Db_Pdo::execute("DELETE FROM survey_user_map WHERE user_id = ?", $this->user_id);
            Db_Pdo::execute("DELETE FROM notification_message_user_map WHERE user_id = ?", $this->user_id);
            Db_Pdo::execute("DELETE FROM user_social WHERE user_id = ?", $this->user_id);
            Db_Pdo::execute("DELETE FROM user_address WHERE user_id = ?", $this->user_id);
            Db_Pdo::execute("UPDATE user SET created = now(), username = '' WHERE id = ?", $this->user_id);

            $newGamer = Gamer::reset($this->user_id, $this->user_key, $this->starbar_id);
            Game_Starbar::getInstance()->install();

            $user = new User();
            $user->loadData($this->user_id);
            return $this->_resultType($user);
        } else {
            return $this->_resultType(false);
        }
    }

}


