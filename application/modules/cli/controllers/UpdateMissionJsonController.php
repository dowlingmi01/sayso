<?php
require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Cli_UpdateMissionJsonController extends Api_GlobalController
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
	
	static function exception_error_handler($errno, $errstr, $errfile, $errline ) {
	    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
	}

	private function _importMissionJson() {
		set_error_handler("Cli_UpdateMissionJsonController::exception_error_handler");  
		try {
			$mission_short_name = $_SERVER['argv'][2];
			
			if( ! $mission_short_name )
				throw new Exception("Missing mission_short_name.");
				
			$fileLocation = realpath(APPLICATION_PATH . '/../public/client/machinima/landing/missions/models');
			if( ! is_writable($fileLocation) )
				throw new Exception("Models dir is not writable.");
			
			$filePath =  $fileLocation . '/' . $mission_short_name . '.pre.json';
			$fileContents = file_get_contents($filePath);
			if( $fileContents === FALSE )
				throw new Exception("Could not read pre.json file.");

			$missionData = Zend_Json::decode($fileContents);
			
			$filePath =  $fileLocation . '/' . $mission_short_name . '.json';
			$fileContents = file_get_contents($filePath);
			if( $fileContents === FALSE )
				throw new Exception("Could not read json file.");

			$missionDataPrev = Zend_Json::decode($fileContents);
			
			$messages = array();
			
			foreach( $missionData['stages'] as $ind=>&$stage ) {
				if( array_key_exists('question', $stage['data']) )
					$this->_processMissionQuestion( $stage['data'], $missionDataPrev['stages'][$ind]['data'] );
				else
					foreach($stage['data']['questions'] as $indq => &$question)
						$this->_processMissionQuestion( $question, $missionDataPrev['stages'][$ind]['data']['questions'][$indq] );
			}
			$json = Zend_Json::encode($missionData);

			$filePath = $fileLocation . '/' . $mission_short_name . '.json';
			$filePath2 = $fileLocation . '/' . $mission_short_name . '.bak.json';
			if( !rename($filePath, $filePath2) )
				throw new Exception("Error renaming json file");
			$result = file_put_contents($filePath, $json);
			if( $result === FALSE )
				throw new Exception("Error writing file");
				
			$messages[] = Zend_Json::prettyPrint($json, array("indent" => "   "));
			restore_error_handler();
			return $messages;
		} catch( Exception $e ) {
			restore_error_handler();
			return array('Error: ' . $e->getMessage());
		}
	}
	private function _processMissionQuestion( &$question, $questionPrev ) {
		$question['question']['id'] = $questionPrev['question']['id'];
		$correct = array_key_exists('correct', $question) ? $question['correct'] : -1;
		
		foreach($question['answers'] as $ind=>&$answer) {
			if( $answer['id'] == $correct )
				$question['correct'] = $questionPrev['answers'][$ind]['id'];
			$answer['id'] = $questionPrev['answers'][$ind]['id'];
		}
	}
}