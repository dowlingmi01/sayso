<?php
/**
 * <p>Survey endpoiints</p>
 *
 * @package Api3
 * @subpackage endpoint
 */
class Api3_SurveyEndpoint extends Api3_GlobalController
{
	/**
	 * Gets survey data
	 *
	 * <p><b>required params: </b>
	 *	survey_id
	 *	send_questions
	 *	send_question_choices</p>
	 *
	 * @param Api3_EndpointRequest $request
	 * @return \Api3_EndpointResponse
	 */
	public function getSurvey(Api3_EndpointRequest $request)
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

		$response = new Api3_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		$surveyId			= (int)$request->validParameters["survey_id"];

		$surveyObject = new Survey();
		$surveyObject->loadData($surveyId);
		$survey = $surveyObject->getData();

		//add questions and answer choices
		if ($request->validParameters["send_questions"])
		{
			$questions = new Survey_QuestionCollection();
			$questions->loadAllQuestionsForSurvey($surveyId);
			$questionData = $response->getRecordsFromCollection($questions);

			if ($request->validParameters["send_question_choices"])
				{
				$choices = new Survey_QuestionChoiceCollection();
				$choices->loadAllChoicesForSurvey($surveyId);
				$choiceData = $response->getRecordsFromCollection($choices);

				//merge questions and choices
				foreach ($choiceData as $key => $value) {
					$questionData[$value["survey_question_id"]]["choices"][$key] = $value;
				}
			}
			$survey["questions"] = $questionData;
		}
		//check for mission or trailer data
		switch ($survey["type"])
		{
			case "mission":
				$mission = new Survey_MissionInfo();
				$mission->loadDataBySurveyId($surveyId);
				$missionData = $mission->getData();
				$survey["mission_data"] = $missionData;
				break;
			case "trailer":
				$trailer = new Survey_TrailerInfo();
				$trailer->loadDataBySurveyId($surveyId);
				$trailerData = $trailer->getData();
				$survey["trailer_data"] = $trailerData;
				break;
			default:
		}

		$cleanSurvey = array($surveyId => $this->_cleanSurveyResponse($survey, $surveyId));
		$response->addRecordsFromArray($cleanSurvey);

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
	 * @param Api3_EndpointRequest $request
	 * @return \Api3_EndpointResponse
	 */
	public function getSurveys(Api3_EndpointRequest $request)
	{
		$validators = array(
				"starbar_id"		=> "int_required_notEmpty",
				"survey_type"		=> "alpha_required_notEmpty",
				"survey_status"		=> "alpha_notEmpty",
				"page_number"		=> "int_required_notEmpty",
				"results_per_page"	=> "int_required_notEmpty"
			);
		$filters = array();

		$response = new Api3_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		$starbarId			= $request->validParameters["starbar_id"];
		$userId			= $request->auth->userData->user_id;
		$type			= $request->validParameters["survey_type"];
		$surveyUserStatus	= isset($request->validParameters["survey_status"]) ? $request->validParameters["survey_status"] : NULL;

		$surveyCollection = new SurveyCollection();
		//TODO: refactor this function to accept pagination at this level instead of getting the entire result set and parsing it down.
		$surveyCollection->loadSurveysForStarbarAndUser ($starbarId, $userId, $type, $surveyUserStatus);

		$surveys = $response->getRecordsFromCollection($surveyCollection);

		$surveyData = array();
		$otherEndpointData = array();
		foreach ($surveys as $key => $value) {
			$params = array("survey_id" => $key);
			$otherEndpointData = $response->getFromOtherEndpoint("getSurvey", get_class(), $params, $this->request_name);
			$surveyData[$key] = $otherEndpointData->records->$key;
		}

		$paginatedSurveyData = $response->paginateArray($surveyData, $request);
		$response->addRecordsFromArray($paginatedSurveyData);
		$response->setPagination(count($surveyData));

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
	 * @param Api3_EndpointRequest $request
	 * @return \Api3_EndpointResponse
	 */
	public function getSurveysCounts(Api3_EndpointRequest $request)
	{
		$validators = array(
				"starbar_id"		=> "int_required_notEmpty",
				"survey_type"		=> "alpha_notEmpty",
				"survey_status"		=> "alpha_notEmpty"
			);
		$filters = array();

		$response = new Api3_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		$surveyType		= $request->validParameters["survey_type"];
		$starbarId			= $request->validParameters["starbar_id"];
		$userId			= $request->auth->userData->user_id;
		$status			= isset($request->validParameters["survey_status"]) ? $request->validParameters["survey_status"] : "active";

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
	 * <p>The ontents of survey_data depend on the type of survey submitted</p>
	 *
	 * @param Api3_EndpointRequest $request
	 * @return \Api3_EndpointResponse
	 * @throws Exception
	 * @see /applications/models/Surey/Response.php updateResponse()
	 */
	public function updateSurveyResponse(Api3_EndpointRequest $request)
	{
		$validators = array(
				"starbar_id"			=> "int_required_notEmpty",
				"survey_id"			=> "int_required_notEmpty",
				"survey_response_id"		=> "int_required_notEmpty"
			);
		$filters = array();

		$response = new Api3_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		$surveyId			= $request->validParameters["survey_id"];
		$starbarId			= $request->validParameters["starbar_id"];
		$surveyResponseId	= $request->validParameters["survey_response_id"];
		$userId			= $request->auth->userData->user_id;
		$userKey			= $request->auth->userData->user_key;

		$surveyResponse = new Survey_Response();
		$surveyResponse->loadData($surveyResponseId);

		//build $data - common attributes
		$data = array(
			"user_id"		=> $userId,
			"survey_id"	=> $surveyId,
			"starbar_id"	=> $starbarId,
			"user_id"		=> $userId,
			"user_key"		=> $userKey
		);
		//add to $data based on $response->submittedParameters["survey_data"]
		if (isset($request->submittedParameters->survey_data))
		{
			$surveyData = $request->submittedParameters->survey_data;
			if (!is_object($surveyData) && !is_array($surveyData))
				throw new Exception('Invalid $surveyData.');
			foreach ($surveyData as $key => $value)
			{
				if (array_key_exists($key, $data))
					throw new Exception('Invalid $surveyData. Trying to overwrite data.');
				$data[$key] = $value;
			}
		}
		$updateResponse = $surveyResponse->updateResponse($data);

		if (!$updateResponse)
			throw new Exception('Survey update failed.');

		$response->setResultVariables($updateResponse);
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
	 * @param Api3_EndpointRequest $request
	 * @return \Api3_EndpointResponse
	 *
	 * @todo add options for next survey and results
	 */
	public function updateSurveyStatus(Api3_EndpointRequest $request)
	{
		//TODO: validate survey_status and processing_status against the enum in the db
		$validators = array(
			"survey_response_id"		=> "int_required_notEmpty",
			"survey_status"			=> "alpha_required_notEmpty",
			"processing_status"		=> "alpha_required_notEmpty",
			"downloaded"			=> "required_allowEmpty"
			);
		$filters = array(
			"downloaded"			=> "bool"
			);

		$response = new Api3_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		$surveyResponseId	= $request->validParameters["survey_response_id"];
		$surveyStatus		= $request->validParameters["survey_status"];
		$processingStatus	= $request->validParameters["processing_status"];
		$downloaded		= $request->validParameters["downloaded"] === TRUE ? new Zend_Db_Expr('now()') : NULL;

		$surveyResponse = new Survey_Response();
		$surveyResponse->loadData($surveyResponseId);
		$updateResponse = $surveyResponse->updateSurveyStatus($surveyStatus, $processingStatus, $downloaded);

		$successMessage = $updateResponse === FALSE ? FALSE : TRUE;
		$response->setResultVariable("success", $successMessage);

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
	 * @param Api3_EndpointRequest $request
	 * @return \Api3_EndpointResponse
	 */
	public function shareSurvey(Api3_EndpointRequest $request)
	{
		$validators = array(
			"survey_id"			=> "int_required_notEmpty",
			"starbar_id"			=> "int_required_notEmpty",
			"shared_type"			=> "alpha_required_notEmpty",
			"network"				=> "alpha_required_notEmpty"
			);
		$filters = array();

		$response = new Api3_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		$surveyId			= $request->validParameters["survey_id"];
		$starbarId			= $request->validParameters["starbar_id"];
		$userId			= $request->auth->userData->user_id;
		$sharedType		= $request->validParameters["shared_type"];
		$network			= $request->validParameters["network"];

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
			"external_question_id"	=> ""
		);

		$questionChoiceFieldsToRemove = array(
			"external_choice_id"		=> "",
			"survey_question_id"		=> ""
		);

		$newSurvey = array_diff_key($survey, $surveyFieldsToRemove);

		//step through each of the possible layers of the survey array
		if (isset($survey["questions"]))
		{
			foreach ($survey["questions"] as $key => $value)
			{
				$newSurvey["questions"][$key] = array_diff_key($value, $questionFieldsToRemove);

				if (isset($value["choices"]))
				{
					foreach ($value["choices"] as $choiceKey => $choiceValue)
					{
						$newSurvey["questions"][$key]["choices"][$choiceKey] = array_diff_key($choiceValue, $questionChoiceFieldsToRemove);
					}
				}
			}
		}

		//add surveyResponseId
		$surveyResponse = new Survey_Response();
		$surveyResponse->loadDataByUniqueFields(array("survey_id"=> $surveyId));
		$newSurvey["survey_response_id"] = $surveyResponse->id;

		return $newSurvey;
	}
}