<?php
/**
 * <p>Survey endpoiints</p>
 *
 * @package Ssmart
 * @subpackage endpoint
 */
class Ssmart_Panelist_SurveyEndpoint extends Ssmart_GlobalController
{
	/**
	 * Gets survey data
	 *
	 * <p><b>required params: </b>
	 *	survey_id
	 *	send_questions
	 *	send_question_choices</p>
	 *
	 * @param Ssmart_EndpointRequest $request
	 * @return \Ssmart_EndpointResponse
	 */
	public function getSurvey(Ssmart_EndpointRequest $request)
	{
		$validators = array(
				"survey_id"			=> "int_required_notEmpty",
				"send_questions"		=> "required_allowEmpty",
				"send_question_choices"	=> "required_allowEmpty"
			);
		$filters = array(
				"send_questions"		=> "bool",
				"send_question_choices"	=> "bool"
			);

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		$surveyId			= (int)$request->valid_parameters["survey_id"];
		$userId				= $request->auth->user_data->user_id;

		// @TODO IMPORTANT! this should use getSurveys() or equivalent to ensure that the user is allowed to get this survey!
		$survey = new Survey();
		$survey->loadData($surveyId);

		//add surveyResponseId
		$surveyResponse = new Survey_Response();
		$surveyResponse->loadDataByUniqueFields(["survey_id" => $surveyId, "user_id" => $userId]);

		$survey->setRewardPoints($surveyResponse->status);

		$surveyData = $survey->getData();
		$surveyData["id"] = $surveyId;
		$surveyData['survey_response_id'] = $surveyResponse->id;

		//add questions and answer choices
		if ($request->valid_parameters["send_questions"])
		{
			$i = 0;
			$questions = new Survey_QuestionCollection();
			$questions->loadAllQuestionsForSurvey($surveyId);

			$questionData = [];
			/** @var $question Survey_Question  */
			foreach ($questions as $question) {
				$questionData[$i] = $question->toArray();
				$questionData[$i]['id'] = $question->id;

				if ($request->valid_parameters["send_question_choices"]) {
					$choices = new Survey_QuestionChoiceCollection();
					$choices->loadAllChoicesForSurvey($surveyId);
					$choiceData = [];
					$j = 0;
					/** @var $choice Survey_QuestionChoice  */
					foreach ($choices as $choice) {
						if ($questionData[$i]['id'] == $choice->survey_question_id) {
							$choiceData[$j] = $choice->toArray();
							$choiceData[$j]['id'] = $choice->id;
						}
						$j++;
					}
					$questionData[$i]['choices'] = $choiceData;

					$i++;
				}
			}
			$surveyData["questions"] = $questionData;
		}
		//check for mission or trailer data
		switch ($surveyData["type"])
		{
			case "mission":
				$mission = new Survey_MissionInfo();
				$mission->loadDataBySurveyId($surveyId);
				$missionData = $mission->getData();
				$surveyData["mission_data"] = $missionData;
				break;
			case "trailer":
				$trailer = new Survey_TrailerInfo();
				$trailer->loadDataBySurveyId($surveyId);
				$trailerData = $trailer->getData();
				$surveyData["trailer_data"] = $trailerData;
				break;
			default:
		}

		$cleanSurveyData = $this->_cleanSurveyResponse($surveyData, $surveyId);
		$response->setResultVariable('survey', $cleanSurveyData);


		return $response;
	}

	/**
	 * Gets multiple surveys.
	 *
	 * <p><b>required params: </b>
	 *	starbar_id
	 *	survey_type
	 * <b>optional params
	 *	page_number
	 *	results_per_page</p>
	 *
	 * @param Ssmart_EndpointRequest $request
	 * @return \Ssmart_EndpointResponse
	 */
	public function getSurveys(Ssmart_EndpointRequest $request)
	{
		$validators = array(
				"starbar_id"		=> "int_required_notEmpty",
				"survey_type"		=> "alpha_required_notEmpty",
				"survey_status"		=> "alpha_required_notEmpty",
				"page_number"		=> "int_required_notEmpty",
				"results_per_page"	=> "int_required_notEmpty"
			);
		$filters = array();

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		$starbarId			= $request->valid_parameters["starbar_id"];
		$userId			= $request->auth->user_data->user_id;
		$type			= $request->valid_parameters["survey_type"];
		$surveyUserStatus	= isset($request->valid_parameters["survey_status"]) ? $request->valid_parameters["survey_status"] : NULL;

		$type = str_replace("surveys", "survey", $type);
		$type = str_replace("polls", "poll", $type);
		$type = str_replace("trailers", "trailer", $type);
		$type = str_replace("quizzes", "quiz", $type);

		if (in_array($type, ["poll", "survey"]) && in_array($surveyUserStatus, ["new", "archived"])) {
			Survey_ResponseCollection::markOldSurveysArchivedForStarbarAndUser($starbarId, $userId, $type);
			Survey_ResponseCollection::markUnseenSurveysNewForStarbarAndUser($starbarId, $userId, $type);
		}

		$surveyCollection = new SurveyCollection();
		//TODO: refactor this function to accept pagination at this level instead of getting the entire result set and parsing it down.
		$surveyCollection->loadSurveysForStarbarAndUser ($starbarId, $userId, $type, $surveyUserStatus);

		foreach ($surveyCollection as $survey) {
			$survey->setRewardPoints($surveyUserStatus);
		}

		$response->addRecordsFromCollection($surveyCollection);

		return $response;
	}

	/**
	 * Gets the count of how many surveys a user has.
	 *
	 * <p><b>required params: </b>
	 *	starbar_id
	 *	survey_type
	 * <b>optional params
	 *	survey_status</p>
	 *
	 * @param Ssmart_EndpointRequest $request
	 * @return \Ssmart_EndpointResponse
	 */
	public function getSurveyCounts(Ssmart_EndpointRequest $request)
	{
		$validators = array(
				"starbar_id"		=> "int_required_notEmpty",
				"survey_type"		=> "alpha_required_notEmpty",
				"survey_status"		=> "alpha_required_notEmpty"
			);
		$filters = array();

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		$surveyType		= $request->valid_parameters["survey_type"]; //TODO: make this optional [Why? -- Hamza]
		$starbarId			= $request->valid_parameters["starbar_id"];
		$userId			= $request->auth->user_data->user_id;
		$status			= $request->valid_parameters["survey_status"];

		$count = Survey_ResponseCollection::countUserSurveys($userId, $starbarId, $surveyType, $status);

		$response->setResultVariable("count", $count);

		return $response;
	}

	/**
	 * Updates a survey response
	 *
	 * <p><b>required params: </b>
	 *	starbar_id
	 *	survey_id
	 *	survey_response_id
	 *	survey_data</p>
	 * <p>The ontents of survey_data are question_id question_choice_id pairs</p>
	 *
	 * @param Ssmart_EndpointRequest $request
	 * @return \Ssmart_EndpointResponse
	 * @throws Exception
	 * @see /applications/models/Surey/Response.php updateResponse()
	 */
	public function updateSurveyResponse(Ssmart_EndpointRequest $request)
	{
		$validators = array(
				"starbar_id"			=> "int_required_notEmpty",
				"survey_id"			=> "int_required_notEmpty",
				"survey_response_id"		=> "int_required_notEmpty"
			);
		$filters = array();

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		$surveyId			= $request->valid_parameters["survey_id"];
		$starbarId			= $request->valid_parameters["starbar_id"];
		$surveyResponseId	= $request->valid_parameters["survey_response_id"];
		$userId			= $request->auth->user_data->user_id;

		$survey = new Survey();
		$survey->loadData($surveyId);

		$surveyResponse = new Survey_Response();
		$surveyResponse->loadData($surveyResponseId);

		if (!$surveyResponse->id || $surveyResponse->user_id != $userId || ($surveyResponse->status != "new" && $surveyResponse->status != "archived"))
			throw new Exception('Invalid survey (already completed?).');

		//build $data - common attributes
		$data = array(
			"user_id"		=> $userId,
			"survey_id"	=> $surveyId,
			"starbar_id"	=> $starbarId,
			"user_id"		=> $userId
		);
		//add to $data based on $response->submitted_parameters["survey_data"]
		if (isset($request->submitted_parameters->survey_data))
		{
			$surveyData = $request->submitted_parameters->survey_data;
			if (!is_object($surveyData) && !is_array($surveyData))
				throw new Exception('Invalid $surveyData.');
			foreach ($surveyData as $key => $value)
			{
				if (array_key_exists($key, $data))
					throw new Exception('Invalid $surveyData. Trying to overwrite data.');
				$data[$key] = $value;
			}
		}

		$updatedStatus = $surveyResponse->updateResponse($data);

		// run the right transaction
		if ($updatedStatus == "completed")
			Game_Transaction::completeSurvey($userId, $starbarId, $survey);
		else if ($updatedStatus == "disqualified")
			Game_Transaction::disqualifySurvey($userId, $starbarId, $survey);

		$response->setResultVariable("success", TRUE);

		//add game data to the response
		$economyId = Economy::getIdforStarbar($starbarId);
		$commonDataParams = array("user_id" => $userId, "economy_id" => $economyId);
		$response->addCommonData("game", $commonDataParams);

		return $response;
	}

	/**
	 * Updates the status of a survey.
	 *
	 * <p><b>required params: </b>
	 *	survey_response_id
	 *	survey_status
	 *	processing_status
	 *	downloaded</p>
	 *
	 * @param Ssmart_EndpointRequest $request
	 * @return \Ssmart_EndpointResponse
	 *
	 * @todo add options for next survey and results
	 */
	public function updateSurveyStatus(Ssmart_EndpointRequest $request)
	{
		//TODO: validate survey_status and processing_status against the enum in the db
		$validators = array(
			"starbar_id"			=> "int_required_notEmpty",
			"survey_id"				=> "int_required_notEmpty",
			"survey_response_id"	=> "int_required_notEmpty",
			"survey_status"			=> "alpha_required_notEmpty",
			);
		$filters = array(
			"downloaded"			=> "bool"
			);

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		$surveyId			= $request->valid_parameters["survey_id"];
		$surveyResponseId	= $request->valid_parameters["survey_response_id"];
		$surveyStatus		= $request->valid_parameters["survey_status"];
		$starbarId			= $request->valid_parameters["starbar_id"];
		$userId				= $request->auth->user_data->user_id;

		$survey = new Survey();
		$survey->loadData($surveyId);

		$surveyResponse = new Survey_Response();
		$surveyResponse->loadData($surveyResponseId);

		if (
			!$survey->id
			|| !$surveyResponse->id
			|| ($surveyResponse->status != "new" && $surveyResponse->status != "archived") // survey should currently be new or archived
			|| ($surveyStatus != "completed" && $surveyStatus != "disqualified") // and should change to completed or disqualified
			|| $surveyResponse->survey_id != $survey->id // someone is guessing survey response id?
			|| $surveyResponse->user_id != $userId
		) {
			throw new Exception('Access denied to update survey status');
		}

		// before saving the new status, get the next survey
		$nextSurvey = Survey::getNextSurveyForUser($survey, $userId);
		$nextSurveyData = new stdClass();
		$nextSurveyData->id = $nextSurvey->id;
		$nextSurveyData->title = $nextSurvey->title;
		$nextSurveyData->size = $nextSurvey->size;

		$surveyResponse->status = $surveyStatus;

		if ($survey->origin == "SurveyGizmo")
			$surveyResponse->processing_status = "pending";
		else
			$surveyResponse->processing_status = "not required";

		$surveyResponse->completed_disqualified = new Zend_Db_Expr('now()');
		$surveyResponse->save();

		// run the right transaction
		if ($surveyStatus == "completed")
			Game_Transaction::completeSurvey($userId, $starbarId, $survey);
		else
			Game_Transaction::disqualifySurvey($userId, $starbarId, $survey);

		$response->setResultVariable("success", true);
		$response->setResultVariable("next_survey", $nextSurveyData);

		//add game data to the response
		$economyId = Economy::getIdforStarbar($starbarId);
		$commonDataParams = array("user_id" => $userId, "economy_id" => $economyId);
		$response->addCommonData("game", $commonDataParams);

		return $response;
	}

	/**
	 * Performs actions for when a user shares a survey.
	 *
	 * <p><b>required params: </b>
	 *	survey_id
	 *	starbar_id
	 *	shared_type
	 *	network</p>
	 *
	 * @param Ssmart_EndpointRequest $request
	 * @return \Ssmart_EndpointResponse
	 */
	public function shareSurvey(Ssmart_EndpointRequest $request)
	{
		$validators = array(
			"survey_id"			=> "int_required_notEmpty",
			"starbar_id"			=> "int_required_notEmpty",
			"shared_type"			=> "alpha_required_notEmpty",
			"network"				=> "alpha_required_notEmpty"
			);
		$filters = array();

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		$surveyId			= $request->valid_parameters["survey_id"];
		$starbarId			= $request->valid_parameters["starbar_id"];
		$userId			= $request->auth->user_data->user_id;
		$sharedType		= $request->valid_parameters["shared_type"];
		$network			= $request->valid_parameters["network"];

		$transactionId = Game_Transaction::share($userId, $starbarId, $sharedType, $network, $surveyId);

		if ($transactionId)
		{
			$response->setResultVariable("success", TRUE);
			$response->setResultVariable("transaction_id", $transactionId);
		} else {
			$response->setResultVariable("success", FALSE);
		}

		//add game data to the response
		$economyId = Economy::getIdforStarbar($starbarId);
		$commonDataParams = array("user_id" => $userId, "economy_id" => $economyId);
		$response->addCommonData("game", $commonDataParams);

		return $response;
	}

//////////Helper functions/////////////

	/**
	 * Removes unecessary fields field names
	 * on the survey object.
	 *
	 * @param array $survey
	 * @param int $surveyId
	 * @return type
	 */
	private function _cleanSurveyResponse($survey, $surveyId)
	{
		//remove fields not needed
		$surveyFieldsToRemove = array(
			"user_id"				=> "",
			"starbar_id"			=> "",
			"premium"				=> "",
			"ordinal"				=> "",
			"start_after"			=> "",
			"start_at"				=> "",
			"end_at"				=> "",
			"processing_status"		=> "",
			"start_day"				=> "",
			"status"				=> "",
			"last_response"			=> "",
			"report_cell_id"			=> ""
		);

		$questionFieldsToRemove = array(
			"survey_id"			=> "",
		);

		$questionChoiceFieldsToRemove = array(
			"survey_question_id"		=> ""
		);

		$newSurvey = array_diff_key($survey, $surveyFieldsToRemove);

		//step through each of the possible layers of the survey array
		if (isset($survey["questions"]))
		{
			$i = 0;
			foreach ($survey["questions"] as $value)
			{
				$newSurvey["questions"][$i] = array_diff_key($value, $questionFieldsToRemove);

				if (isset($value["choices"]))
				{
					$newSurvey["questions"][$i]["choices"] = [];
					foreach ($value["choices"] as $choiceValue)
					{
						$newSurvey["questions"][$i]["choices"][] = array_diff_key($choiceValue, $questionChoiceFieldsToRemove);
					}
				}

				$i++;
			}
		}

		return $newSurvey;
	}
}