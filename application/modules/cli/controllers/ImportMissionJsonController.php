<?php
require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Cli_ImportMissionJsonController extends Api_GlobalController
{

	public function init()
	{
		if (PHP_SAPI != 'cli')
		{
			throw new Exception("Unsupported call!");
		}
	}

	/**
	 * All function calls should go here
	 */
	public function runAction()
	{
		$messages = $this->_importMissionJson();
		foreach( $messages as $message )
			echo( $message . "\n" );

		// End cli actions with this, otherwise you get a fatal error
		exit(0);
	}
	private function _importMissionJson() {
		try {
			$mission_short_name = $_SERVER['argv'][2];
			$override = (array_key_exists(3, $_SERVER['argv']) && $_SERVER['argv'][3] == 'override');
			
			if( ! $mission_short_name )
				throw new Exception("Missing mission_short_name.");
				
			$fileLocation = realpath(APPLICATION_PATH . '/../public/client/machinima/landing/missions/models');
			if( ! is_writable($fileLocation) )
				throw new Exception("Models dir is not writable.");
			
			$filePath =  $fileLocation . '/' . $mission_short_name . '.pre.json';
			$fileContents = file_get_contents($filePath);
			if( $fileContents === FALSE )
				throw new Exception("Could not read json file.");

			$missionData = Zend_Json::decode($fileContents);
			
			$missionInfo = new Survey_MissionInfo();
			$missionInfo->loadDataByUniqueFields(array('short_name'=>$mission_short_name));
			
            Record::beginTransaction();

			if( $missionInfo->id ) {
				if( !$override )
					throw new Exception("Mission data already exists. Specify override param to delete it.");
				$survey = new Survey();
				$survey->loadData($missionInfo->survey_id);
				$survey->delete();
			}
			
			$messages = array();
			$survey = new Survey();
			$survey->type = 'mission';
			$survey->origin = 'internal';
			$survey->title = $missionData['title'];
			$survey->custom_reward_experience = $missionData['reward_experience'];
			$survey->custom_reward_redeemable = $missionData['reward_redeemable'];
			$survey->save();
			$messages[] = "New survey id: ". $survey->id;
			
			$missionInfo = new Survey_MissionInfo();
			$missionInfo->survey_id = $survey->id;
			$missionInfo->short_name = $mission_short_name;
			$missionInfo->number_of_stages = count($missionData['stages']);
			$missionInfo->save();
			$messages[] = "New survey_mission_info id: ". $missionInfo->id;
			
			$starbarSurveyMap = new Starbar_SurveyMap();
			$starbarSurveyMap->starbar_id = 4;
			$starbarSurveyMap->survey_id = $survey->id;
			$starbarSurveyMap->save();
			
			foreach( $missionData['stages'] as &$stage ) {
				if( array_key_exists('question', $stage['data']) )
					$this->_processMissionQuestion( $stage['data'], $survey->id );
				else
					foreach($stage['data']['questions'] as &$question)
						$this->_processMissionQuestion( $question, $survey->id );
			}
			$json = Zend_Json::encode($missionData);

			$filePath = $fileLocation . '/' . $mission_short_name . '.json';
			$result = file_put_contents($filePath, $json);
			if( $result === FALSE )
				throw new Exception("Error writing file");
				
            Record::commitTransaction();
				
			$messages[] = Zend_Json::prettyPrint($json, array("indent" => "   "));
			return $messages;
		} catch( Exception $e ) {
			return array('Error: ' . $e->getMessage());
		}
	}
	private function _processMissionQuestion( &$question, $survey_id ) {
		$surveyQuestion = new Survey_Question();
		$surveyQuestion->survey_id = $survey_id;
		$surveyQuestion->choice_type = 'single';
		$surveyQuestion->title = $question['question']['text'];
		$surveyQuestion->number_of_choices = count($question['answers']);
		$surveyQuestion->save();
		$question['question']['id'] = $surveyQuestion->id;
		$correct = array_key_exists('correct', $question) ? $question['correct'] : -1;
		
		foreach($question['answers'] as &$answer) {
			$surveyQuestionChoice = new Survey_QuestionChoice();
			$surveyQuestionChoice->survey_question_id = $surveyQuestion->id;
			$surveyQuestionChoice->title = $answer['text'];
			$surveyQuestionChoice->value = $answer['text'];
			if( $answer['id'] == $correct )
				$surveyQuestionChoice->correct = 1;
			$surveyQuestionChoice->save();
			if( $answer['id'] == $correct )
				$question['correct'] = $surveyQuestionChoice->id;
			$answer['id'] = $surveyQuestionChoice->id;
		}
	}
}