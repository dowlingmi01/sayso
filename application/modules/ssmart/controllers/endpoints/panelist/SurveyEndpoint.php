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

		$surveyObject = new Survey();
		$surveyObject->loadData($surveyId);
		$survey = $surveyObject->getData();
		$survey["id"] = $surveyId;

		//add questions and answer choices
		if ($request->valid_parameters["send_questions"])
		{
			$questions = new Survey_QuestionCollection();
			$questions->loadAllQuestionsForSurvey($surveyId);
			$questionData = $response->getRecordsFromCollection($questions);

			if ($request->valid_parameters["send_question_choices"])
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
	 * @param Ssmart_EndpointRequest $request
	 * @return \Ssmart_EndpointResponse
	 */
	public function getSurveys(Ssmart_EndpointRequest $request)
	{
		$validators = array(
				"starbar_id"		=> "int_required_notEmpty",
				"survey_type"		=> "alpha_required_notEmpty",
				"survey_status"		=> "alpha_notEmpty",
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

		$surveyCollection = new SurveyCollection();
		//TODO: refactor this function to accept pagination at this level instead of getting the entire result set and parsing it down.
		$surveyCollection->loadSurveysForStarbarAndUser ($starbarId, $userId, $type, $surveyUserStatus);

		$surveys = $response->getRecordsFromCollection($surveyCollection);

		$surveyData = array();
		$otherEndpointData = array();
		foreach ($surveys as $key => $value) {
			$params = array("survey_id" => $key);
			$otherEndpointData = $response->getFromOtherEndpoint("getSurvey", get_class(), $params, $this->request_name);
			$surveyData[] = $otherEndpointData->records[0];
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
	 * @param Ssmart_EndpointRequest $request
	 * @return \Ssmart_EndpointResponse
	 */
	public function getSurveysCounts(Ssmart_EndpointRequest $request)
	{
		$validators = array(
				"starbar_id"		=> "int_required_notEmpty",
				"survey_type"		=> "alpha_required_notEmpty",
				"survey_status"		=> "alpha_notEmpty"
			);
		$filters = array();

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		$surveyType		= $request->valid_parameters["survey_type"]; //TODO: make this optional
		$starbarId			= $request->valid_parameters["starbar_id"];
		$userId			= $request->auth->user_data->user_id;
		$status			= isset($request->valid_parameters["survey_status"]) ? $request->valid_parameters["survey_status"] : "active";

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
		$userKey			= $request->auth->user_data->user_key;

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

		$updateResponse = $surveyResponse->updateResponse($data);

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
	 * @param Ssmart_EndpointRequest $request
	 * @return \Ssmart_EndpointResponse
	 *
	 * @todo add options for next survey and results
	 */
	public function updateSurveyStatus(Ssmart_EndpointRequest $request)
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

		$response = new Ssmart_EndpointResponse($request, $filters, $validators);

		if ($response->hasErrors())
			return $response;

		//logic
		$surveyResponseId	= $request->valid_parameters["survey_response_id"];
		$surveyStatus		= $request->valid_parameters["survey_status"];
		$processingStatus	= $request->valid_parameters["processing_status"];
		$downloaded		= $request->valid_parameters["downloaded"] === TRUE ? new Zend_Db_Expr('now()') : NULL;

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