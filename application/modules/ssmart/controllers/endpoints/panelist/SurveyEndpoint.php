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
				"starbar_id"			=> "int_required_notEmpty",
				"survey_id"				=> "int_required_notEmpty",
				"send_questions"		=> "required_allowEmpty",
				"send_question_choices"	=> "required_allowEmpty"
			);
		$filters = array(
				"send_questions"		=> "bool",
				"send_question_choices"	=> "bool"
			);

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		//logic
		$starbarId			= (int)$request->getParam("starbar_id");
		$surveyId			= (int)$request->getParam("survey_id");
		$sendQuestions		= $request->getParam("send_questions");
		$sendQuestionChoces	= $request->getParam("send_question_choices");
		$userId				= $request->getUserId();

		$surveyResponse = new Survey_Response();
		$surveyResponse->loadDataByUniqueFields(array("user_id" => $userId, "survey_id" => $surveyId));

		if (!$surveyResponse->id) {
			// Failed... might be because it's a new user. Try to find new surveys of the same type for that user
			$survey = new Survey();
			$survey->loadData($surveyId);

			if ($survey->id) {
				Survey_ResponseCollection::markUnseenSurveysNewForStarbarAndUser($starbarId, $userId, $survey->type);

				// try again
				$surveyResponse->loadDataByUniqueFields(array("user_id" => $userId, "survey_id" => $surveyId));
			}
		}

		if (!$surveyResponse->id) {
			throw new Exception("SURVEY_UNAVAILABLE");
		}

		if ($surveyResponse->status == "completed" || $surveyResponse->status == "disqualified") {
			throw new Exception("SURVEY_COMPLETED");
		}

		$survey = new Survey();
		$survey->loadData($surveyId);

		//add surveyResponseId
		$surveyResponse = new Survey_Response();
		$surveyResponse->loadDataByUniqueFields(["survey_id" => $surveyId, "user_id" => $userId]);

		$survey->setRewardPoints($surveyResponse->status);

		$surveyData = $survey->getData();
		$surveyData["id"] = $surveyId;
		$surveyData['survey_response_id'] = $surveyResponse->id;

		//check for mission or trailer data
		switch ($survey->type)
		{
			case "mission":
				$missionInfo = new Survey_MissionInfo();
				$missionInfo->loadDataBySurveyId($surveyId);
				$missionInfoData = $missionInfo->getData();
				$surveyData["mission_info"] = $missionInfoData;
				break;
			case "trailer":
				$trailerInfo = new Survey_TrailerInfo();
				$trailerInfo->loadDataBySurveyId($surveyId);
				$trailerInfoData = $trailerInfo->getData();
				$surveyData["trailer_info"] = $trailerInfoData;
				break;
			default:
		}

		//add questions and answer choices
		if ($sendQuestions)
		{
			$i = 0;
			$questions = new Survey_QuestionCollection();
			$questions->loadAllQuestionsForSurvey($surveyId);

			$questionData = [];
			/** @var $question Survey_Question  */
			foreach ($questions as $question) {
				$questionData[$i] = $question->toArray();
				$questionData[$i]['id'] = $question->id;

				if ($sendQuestionChoces) {
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
				"survey_status"		=> "alpha_required_notEmpty"
			);
		$filters = array();

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		//logic
		$starbarId 			= $request->getParam("starbar_id");
		$userId				= $request->getUserId();
		$type 				= $request->getParam("survey_type");
		$surveyUserStatus 	= $request->getParam("survey_status");
		$pageNumber 		= (int) $request->getParam("page_number", 1);
		$resultsPerPage 	= (int) $request->getParam("results_per_page", 50);

		// used by trailers (though can be used by any type)
		$chosenSurveyId		= (int) $request->getParam("chosen_survey_id");
		$alwaysChoose		= (int) $request->getParam("always_choose");

		$type = str_replace("surveys", "survey", $type);
		$type = str_replace("polls", "poll", $type);
		$type = str_replace("trailers", "trailer", $type);
		$type = str_replace("quizzes", "quiz", $type);

		if ($type == "trailer")
			$surveyUserStatus = "new";

		if (in_array($surveyUserStatus, ["new", "archived"])) {
			if (in_array($type, ["poll", "survey"])) {
				Survey_ResponseCollection::markOldSurveysArchivedForStarbarAndUser($starbarId, $userId, $type);
			}
			Survey_ResponseCollection::markUnseenSurveysNewForStarbarAndUser($starbarId, $userId, $type);
		}

		$surveyCollection = new SurveyCollection();

		$offset = $this->_calculateOffset($pageNumber, $resultsPerPage);
		$surveyCollection->loadSurveysForStarbarAndUser ($starbarId, $userId, $type, $surveyUserStatus, $resultsPerPage, $offset);

		$surveyIds = "";

		foreach ($surveyCollection as $survey) {
			if ($alwaysChoose && !$chosenSurveyId)
				$chosenSurveyId = $survey->id;

			if ($surveyIds) $surveyIds .= ",";
			$surveyIds .= $survey->id;

			$survey->setRewardPoints($surveyUserStatus);

			if ($survey->id === $chosenSurveyId) {
				$otherEndpointParams = array("survey_id" => $chosenSurveyId, "send_question_choices" => true, "send_questions" => true, "starbar_id" => $starbarId);
				$response->addFromOtherEndpoint("getSurvey", get_class(), $otherEndpointParams, $this->request_name);
			}
		}

		if ($type == "trailer" && $surveyIds) {
			$sql = "SELECT survey_id, video_key FROM survey_trailer_info WHERE survey_id IN ($surveyIds)";
			$results = Db_Pdo::fetchAll($sql);
			$trailerInfo = [];
			foreach ($results as $result) {
				$trailerInfo[$result['survey_id']] = $result;
			}
			$response->setResultVariable("trailer_info", $trailerInfo);
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

		//logic
		$surveyType		= $request->getParam("survey_type"); //TODO: make this optional [Why? -- Hamza]
		$starbarId		= $request->getParam("starbar_id");
		$userId			= $request->getUserId();
		$status			= $request->getParam("survey_status");

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

		//logic
		$surveyId			= $request->getParam("survey_id");
		$starbarId			= $request->getParam("starbar_id");
		$surveyResponseId	= $request->getParam("survey_response_id");
		$userId				= $request->getUserId();

		$survey = new Survey();
		$survey->loadData($surveyId);

		$surveyResponse = new Survey_Response();
		$surveyResponse->loadData($surveyResponseId);

		if (!$surveyResponse->id || $surveyResponse->user_id != $userId || ($surveyResponse->status != "new" && $surveyResponse->status != "archived"))
			throw new Exception('Invalid survey (already completed?).');

		//add to $data based on $response->submitted_parameters["survey_data"]
		if ($surveyData = $request->getParam("survey_data"))
		{
			if (!is_object($surveyData) && !is_array($surveyData))
				throw new Exception('Invalid $surveyData.');
		}

		$updatedStatus = $surveyResponse->updateResponse($surveyData);

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
	 * @throws \Ssmart_EndpointError | Exception
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

		//logic
		$surveyId			= $request->getParam("survey_id");
		$surveyResponseId	= $request->getParam("survey_response_id");
		$surveyStatus		= $request->getParam("survey_status");
		$starbarId			= $request->getParam("starbar_id");
		$userId				= $request->getUserId();

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

	public function updateMissionProgress(Ssmart_EndpointRequest $request) {
		$validators = array(
			"starbar_id"			=> "int_required_notEmpty",
			"top_frame_id"			=> "int_required_notEmpty",
			"mission_short_name"	=> "alnum_required_notEmpty",
			"mission_data"			=> "required_notEmpty",
		);
		$filters = array();
		$response = new Ssmart_EndpointResponse($request, $filters, $validators);
		$result = Survey_MissionProgress::update($request->getUserId(), $request->getParam("starbar_id")
			, $request->getParam("top_frame_id"), $request->getParam("mission_short_name")
			, $request->getParam("mission_data"));

		if( Game_Transaction::wasTransactionExecuted() ) {
			//add game data to the response
			$economyId = Economy::getIdforStarbar($request->getParam("starbar_id"));
			$commonDataParams = array("user_id" => $request->getUserId(), "economy_id" => $economyId);
			$response->addCommonData("game", $commonDataParams);
		}
		$response->setResultVariable("success", $result);
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
			"survey_id"				=> "int_required_notEmpty",
			"starbar_id"			=> "int_required_notEmpty",
			"shared_type"			=> "alpha_required_notEmpty",
			"network"				=> "alpha_required_notEmpty"
			);
		$filters = array();

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		//logic
		$surveyId			= $request->getParam("survey_id");
		$starbarId			= $request->getParam("starbar_id");
		$userId				= $request->getUserId();
		$sharedType			= $request->getParam("shared_type");
		$network			= $request->getParam("network");

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