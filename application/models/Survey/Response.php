<?php


class Survey_Response extends Record
{
	protected $_tableName = 'survey_response';

	/**
	 * @var Survey
	 */
	protected $_survey;

	public function setSurvey (Survey $survey) {
		$this->_survey = $survey;
	}

	/**
	 * @return Survey
	 */
	public function getSurvey() {
		if (!$this->_survey || !$this->_survey->id) {
			if ($this->survey_id) {
				$this->_survey = new Survey();
				$this->_survey->loadData($this->survey_id);
			}
		}
		return $this->_survey;
	}

	public function afterSave() {
		if ($this->id && $this->survey_id) {
			$survey = new Survey();
			$survey->loadData($this->survey_id);
			if ($survey->id) {
				$survey->last_response = new Zend_Db_Expr('now()');
				$survey->save();
			}
		}
	}

	// @todo if you want to return this to the client (e.g. as JSON)
	// then complete the following two methods. Probably also in
	// the Survey class too
	// SEE User class for examples
	// public function exportData()
	// public function exportProperties($parentObject = null)

	public static function checkIfUserHasCompletedSurvey($userId, $surveyId) {
		$sql = "SELECT user_id FROM survey_response WHERE (status = 'completed' OR status = 'disqualified') AND user_id = ? AND survey_id = ?";
		return !!(Db_Pdo::fetch($sql, $userId, $surveyId));
	}

	/**
	* This function checks to see if the user can see a specific survey
	*
	* @param int $userId
	* @param int $surveyId
	*/
	public static function canUserSeeSurvey($userId, $surveyId) {
		$sql = "SELECT user_id FROM survey_response WHERE status = 'new' AND user_id = ? AND survey_id = ?";
		return !!(Db_Pdo::fetch($sql, $userId, $surveyId));
	}

	/**
	* Add a survey for a user if they do not already have access to the survey
	*
	* @param mixed $userId
	* @param mixed $surveyId
	*/
	public static function addSurveyforUser($userId,$surveyId) {
		if (!self::checkIfUserHasCompletedSurvey($userId,$surveyId)) {
			// Hasn't been completed.
			if (!self::canUserSeeSurvey($userId,$surveyId)) {
				// Hasn't been completed, and user can't see it. Add this survey response record
				$newResponse = new Survey_Response();
				$newResponse->id = null; // Force a new record to be inserted
				$newResponse->survey_id = $surveyId;
				$newResponse->user_id = $userId;
				$newResponse->status = 'new';
				$newResponse->processing_status = 'not required';
				return !!($newResponse->save());
			}
		}
		return false;
	}

	public function deleteQuestionResponses() {
		if (!$this->id) return;
		$sql = "DELETE FROM survey_question_response WHERE survey_response_id = ?";
		Db_Pdo::execute($sql, $this->id);
		return true;
	}

	public function process() {
		if (!$this->id || !$this->survey_id || !$this->user_id || $this->processing_status != "pending") return array("Can't process survey_response (id = " . $this->id . ")");

		$config = Api_Registry::getConfig();

		$decodedJson = false;
		$requestParams = array();

		// Reference arrays
		$questionIdReferenceArray = array();
		$comboExternalIdReferenceArray = array();
		$choiceExternalIdReferenceArray = array();
		$choiceValueReferenceArray = array();

		// Messages to show on interface after processing
		$messages = array();

		$rowsMatchingRegex = 0;
		$rowsMatchingNoRegex = 0;
		$surveyQuestionResponsesSaved = 0;

		$survey = new Survey();
		$survey->loadData($this->survey_id);

		if (!$survey->id && !$survey->external_id) return array("Can't process survey (id = " . $this->survey_id . ")");

		$sgUser = $config->surveyGizmo->api->username;
		$sgPass = $config->surveyGizmo->api->password;

		$requestParams["user:pass"] = $sgUser . ":" . $sgPass;
		$requestParams["page"] = 1;
		$requestParams["resultsperpage"] = 1;

		// For more on SG filters: http://developer.surveygizmo.com/resources/filtering-and-browsing-results/
		$requestParams["filter[field][0]"] = "[url(%22srid%22)]";
		$requestParams["filter[operator][0]"] = "=";
		$requestParams["filter[value][0]"] = $this->id;

		if ($this->status == "completed") {
			$requestParams["filter[field][1]"] = "status";
			$requestParams["filter[operator][1]"] = "=";
			$requestParams["filter[value][1]"] = "Complete";
		} elseif ($this->status == "disqualified") {
			$requestParams["filter[field][1]"] = "status";
			$requestParams["filter[operator][1]"] = "=";
			$requestParams["filter[value][1]"] = "Disqualified";
		}

		if (APPLICATION_ENV == "production") {
			$requestParams["filter[field][2]"] = "[url(%22testing%22)]";
			$requestParams["filter[operator][2]"] = "=";
			$requestParams["filter[value][2]"] = "false";
		} else {
			$requestParams["filter[field][2]"] = "[url(%22base_domain%22)]";
			$requestParams["filter[operator][2]"] = "=";
			$requestParams["filter[value][2]"] = BASE_DOMAIN;

			$requestParams["filter[field][3]"] = "[url(%22testing%22)]";
			$requestParams["filter[operator][3]"] = "=";
			$requestParams["filter[value][3]"] = "true";
		}

		$requestParamString = "";
		foreach ($requestParams as $key => $value) {
			if ($requestParamString) $requestParamString .= "&";
			else $requestParamString = "?";

			$requestParamString .= $key . "=" . $value;
		}

		$url = "https://restapi.surveygizmo.com/v1/survey/" . $survey->external_id . "/surveyresponse" . $requestParamString;
		$messages[] = "Connecting to " . $url;

		set_time_limit(180); // Allow 3 minutes for SG response (excludes local processing time, since we reset timer below)
		$handle = fopen($url, 'r');
		$json = stream_get_contents($handle);

		if ($json) {
			$decodedJson = json_decode($json, true);
		} else {
			$decodedJson = null;
			throw new Api_Exception(Api_Error::create(Api_Error::SURVEYGIZMO_ERROR, 'Attempt to retreive survey responses failed when accessing: ' . $url));
		}

		if (
			$decodedJson
			&& isset($decodedJson['result_ok'])
			&& isset($decodedJson['total_count'])
			&& isset($decodedJson['total_pages'])
			&& isset($decodedJson['data'])
			&& $decodedJson['result_ok'] === true
			&& $decodedJson['total_count']
			&& $decodedJson['total_pages']
			&& count($decodedJson['data'])
		) {
			$messages[] = "Survey Gizmo reports " . $decodedJson['total_count'] . " response on " . $decodedJson['total_pages'] . " page";

			// Initialize reference arrays so we don't have to repeatedly call the DB
			$allSurveyQuestions = new Survey_QuestionCollection();
			$allSurveyQuestions->loadAllQuestionsForSurvey($survey->id);

			$allSurveyQuestionChoices = new Survey_QuestionChoiceCollection();
			$allSurveyQuestionChoices->loadAllChoicesForSurvey($survey->id);

			$surveyQuestionChoices = new Survey_QuestionChoiceCollection();

			// Prepare reference arrays to make finding questions easy
			foreach ($allSurveyQuestions as $surveyQuestion) {
				$questionIdReferenceArray[$surveyQuestion->id] = array("question" => $surveyQuestion, "choices" => array());
				if ($surveyQuestion->choice_type == "multiple") {
					$surveyQuestionChoices->loadAllChoicesForSurveyQuestion($surveyQuestion->id);
					foreach ($surveyQuestionChoices as $surveyQuestionChoice) {
						$comboArrayKey = $surveyQuestion->external_question_id . "-" . $surveyQuestionChoice->external_choice_id;
						$comboExternalIdReferenceArray[$comboArrayKey] = $surveyQuestion;
					}
				} elseif ($surveyQuestion->choice_type == "single" && $surveyQuestion->data_type != "none") {
					$comboArrayKey = $surveyQuestion->external_question_id . "-" . $surveyQuestion->external_pipe_choice_id;
					$comboExternalIdReferenceArray[$comboArrayKey] = $surveyQuestion;
					$surveyQuestionChoices->loadAllChoicesForSurveyQuestion($surveyQuestion->id);
					foreach ($surveyQuestionChoices as $surveyQuestionChoice) {
						if ($surveyQuestionChoice->other) {
							$comboArrayKey = $surveyQuestion->external_question_id . "-" . $surveyQuestionChoice->external_choice_id;
							$comboExternalIdReferenceArray[$comboArrayKey] = $surveyQuestion;
						}
					}
				} else {
					$comboArrayKey = $surveyQuestion->external_question_id . "-" . $surveyQuestion->external_pipe_choice_id;
					$comboExternalIdReferenceArray[$comboArrayKey] = $surveyQuestion;
				}
			}

			// Reference arrays for this survey's choices
			foreach ($allSurveyQuestionChoices as $surveyQuestionChoice) {
				$choiceExternalIdReferenceArray[$surveyQuestionChoice->external_choice_id] = $surveyQuestionChoice;
				$choiceValueReferenceArray[$surveyQuestionChoice->value] = $surveyQuestionChoice;
				$questionIdReferenceArray[$surveyQuestionChoice->survey_question_id]["choices"][$surveyQuestionChoice->value] = $surveyQuestionChoice;
			}

			// How the array keys can look for the responses we want to parse
			$regexArray = array(
				"/\[question\(([0-9]+)\), option\(([0-9]+)\)\]/",
				"/\[question\(([0-9]+)\), option\(\"([0-9]+)-(other)\"\)\]/",
				"/\[question\(([0-9]+)\)\]/",
				"/\[question\(([0-9]+)\), question_pipe\(([0-9]+)\)\]/",
				"/\[variable\(([0-9]+)\)\]/",
				//"/\[variable\(\"([0-9]+)-shown\"\)\]/",
				"/\[variable\(([0-9]+)\), question_pipe\(([0-9]+)\)\]/",
				"/\[variable\(\"([0-9]+)-shown\"\), question_pipe\(([0-9]+)\)\]/",
			);

			// All the responses on this page of results
			$responsesData = $decodedJson['data'];
			// There should only be one response based on the filtering above, so choose that
			$responseData = $responsesData[0];

			// Reset time limit to allow for processing
			set_time_limit(60);
			$externalResponseId = 0;
			$dataToSave = array();

			// Go through this responses's answers (i.e. all the answers this user gave)
			foreach ($responseData as $answerKey => $answerValue) {
				if ($answerKey == "id") {
					$externalResponseId = (int) $answerValue;
				} elseif ($answerKey == "status") {
					if ($answerValue == "Complete" || $answerValue == "Disqualified") {
						continue;
					} else {
						// Skip Partial (or other?) responses
						$externalResponseId = 0;
						$dataToSave = array();
						break;
					}
				} elseif ($answerValue) { // Skip empty answers
					$matches = array(); // array for preg_match() to write to
					$matchFound = false; // boolean, has the answer key matched any of our regular expressions?
					$matchRegex = ""; // The regex expression that successfully matched this answer
					$matchQuestionExternalId = ""; // The matching question id on SG
					$matchChoiceExternalId = ""; // The matching choice id on SG (can be for multiple choice (with multiple user choices, e.g. checkbox) or for piped questions
					$matchComboArrayKey = ""; // either "$matchQuestionExternalId-" (note dash at the end) or "$matchQuestionExternalId-$matchChoiceExternalId"
					foreach ($regexArray as $regex) {
						$numberOfMatchesFound = preg_match($regex, $answerKey, $matches);
						if ($numberOfMatchesFound) {
							$matchFound = true;
							$matchRegex = $regex;

							$matchQuestionExternalId = $matches[1];
							$matchComboArrayKey = $matchQuestionExternalId . "-";

							if (count($matches) >= 3) {
								$matchChoiceExternalId = $matches[2];
								$matchComboArrayKey .= $matchChoiceExternalId;
							}

							if (count($matches) == 4) $otherValue = true;
							else $otherValue = false;

							break;
						}
					}

					// This answer (within a larger response) matches one of our regular expressions, get the choice/typed in answer out of it
					if ($matchFound) {
						$rowsMatchingRegex++;

						if (isset($comboExternalIdReferenceArray[$matchComboArrayKey])) {
							$matchQuestion = $comboExternalIdReferenceArray[$matchComboArrayKey];
						} else {
							$matchQuestion = null;
							$messages[] = "Question matches regex but not found in \$comboExternalIdReferenceArray: Key = " . $answerKey;
						}

						if ($matchQuestion) {
							$matchChoice = null;
							if ($otherValue || (strpos($matchRegex, "variable") === false && $matchQuestion->data_type != "none")) {
								if (isset($questionIdReferenceArray[$matchQuestion->id]["choices"][$answerValue])) $matchChoice = $questionIdReferenceArray[$matchQuestion->id]["choices"][$answerValue];
								else {
									switch($matchQuestion->data_type) {
										case "string":
											$dataToSave[$matchQuestion->id . "-data"] = array($matchQuestion, $answerValue);
											break;
										case "integer":
											$dataToSave[$matchQuestion->id . "-data"] = array($matchQuestion, intval($answerValue));
											break;
										case "decimal":
										case "monetary":
											$dataToSave[$matchQuestion->id . "-data"] = array($matchQuestion, floatval(str_replace("\$", "", $answerValue)));
											break;
										default:
											$messages[] = "Dunno what to do with this row (question id " . $matchQuestion->id . " matched but unknown data type): " . $answerKey . " => " . $answerValue;
											break;
									}
								}
							} else {
								if (strpos($matchRegex, "variable") !== false && isset($choiceExternalIdReferenceArray[$answerValue])) $matchChoice = $choiceExternalIdReferenceArray[$answerValue];
								elseif (count($matches) >= 3 && isset($choiceExternalIdReferenceArray[$matches[2]])) $matchChoice = $choiceExternalIdReferenceArray[$matches[2]];
								elseif (isset($choiceValueReferenceArray[$answerValue])) $matchChoice = $choiceValueReferenceArray[$answerValue];
								else $messages[] = "Dunno what to do with this row: " . $answerKey . " => " . $answerValue;
							}
							if ($matchChoice) $dataToSave[$matchQuestion->id . "-" . $matchChoice->id] = array($matchQuestion, $matchChoice);
						} else {
							$messages[] = "Unexpected result with this row: " . $answerKey . " => " . $answerValue;
						}
					} else {
						$rowsMatchingNoRegex++;
					}
				}
			} // end processing all answers (and non-answer data) for one user's response

			// $dataToSave has been collected... save it!
			if ($externalResponseId && count($dataToSave)) {
				foreach ($dataToSave as $dataKey => $surveyQuestionResponseData) {
					$surveyQuestionResponse = new Survey_QuestionResponse();
					$surveyQuestionResponse->survey_response_id = $this->id;
					$surveyQuestionResponse->survey_question_id = $surveyQuestionResponseData[0]->id;
					if (strpos($dataKey, "-data") !== false) {
						$surveyQuestionResponse->data_type = $surveyQuestionResponseData[0]->data_type;
						switch($surveyQuestionResponse->data_type) {
							case "string":
								$surveyQuestionResponse->response_string = $surveyQuestionResponseData[1];
								break;
							case "integer":
								$surveyQuestionResponse->response_integer = $surveyQuestionResponseData[1];
								break;
							case "decimal":
							case "monetary":
								$surveyQuestionResponse->response_decimal = $surveyQuestionResponseData[1];
								break;
							default:
								$messages[] = "Survey Question Response should have data type but doesn't! Data key = " . $dataKey;
								break;
						}
					} else {
						$surveyQuestionResponse->data_type = "choice";
						$surveyQuestionResponse->survey_question_choice_id = $surveyQuestionResponseData[1]->id;
					}
					$surveyQuestionResponse->save();
					$surveyQuestionResponsesSaved++;
				}

				$this->external_response_id = $externalResponseId;
				$this->data_download = new Zend_Db_Expr('now()');
				$this->processing_status = "completed";
				$this->save();
			} // data for this response (i.e. all the answers for one user) done being saved!
		}

		$messages[] = "";
		$messages[] = "Processing of survey_response id " . $this->id . " (external response id: " . $this->external_response_id . ") complete!";
		$messages[] = "Rows matching one of the regular expressions: " . $rowsMatchingRegex;
		$messages[] = "survey_question_response records saved in DB: " . $surveyQuestionResponsesSaved;
		$messages[] = "Note that rows saved is usually less than rows matching, since some data is repeated in SG's response";
		$messages[] = "Rows not matching any of the regular expressions (non-zero expected): " . $rowsMatchingNoRegex;
		$messages[] = "";

		return $messages;
	}

	/**
	 * Processes a survey response.
	 *
	 * <p>Validates required params,</p>
	 * <p>Processes polls, trailers, and missions.</p>
	 *
	 * @param array $data
	 * @return boolean
	 */
	public function updateResponse($data)
	{
		//validate required params
		if (!isset($this->id)
			|| !$this->id
			|| $this->status == "completed"
			|| $this->status == "disqualified")
			throw new Exception("Cannot update response, missing parameters!");

		// Delete any existing responses (in case of previous partial response, for whatever reason)
		// only if it's not a mission.
		$this->deleteQuestionResponses();

		$surveyQuestions = new Survey_QuestionCollection();
		$surveyQuestions->loadAvailableQuestionsForSurvey($this->survey_id);

		$surveyQuestionChoiceData = $data->answers;

		try
		{
			foreach ($surveyQuestions as $surveyQuestion)
			{
				if (property_exists($surveyQuestionChoiceData, $surveyQuestion->id))
				{
					$choiceId = $surveyQuestionChoiceData->{$surveyQuestion->id};
					// Verify the choice is valid
					$surveyQuestionChoice = new Survey_QuestionChoice();
					$surveyQuestionChoice->loadDataByUniqueFields(array('id' => $choiceId, 'survey_question_id' => $surveyQuestion->id));

					if (!$surveyQuestionChoice->id)
						throw new Exception("Question choice invalid.");

					$surveyQuestionResponse = new Survey_QuestionResponse();
					$surveyQuestionResponse->survey_response_id = $this->id;
					$surveyQuestionResponse->survey_question_id = $surveyQuestion->id;
					$surveyQuestionResponse->survey_question_choice_id = $choiceId;
					$surveyQuestionResponse->data_type = "choice";
					$surveyQuestionResponse->save();
					//TODO: find a way to test for success on save [check for $surveyQuestionResponse->id after saving -- Hamza]

				} else
					throw new Exception("Question choice not found.");
			}

			//post process survey response - set status, run game txn
			//set status of survey response
			//@todo for trailers and missions, this should check that the status is complete and not a partial response!
			$this->status = "completed";
			$this->processing_status = "completed";
			$this->completed_disqualified = new Zend_Db_Expr('now()');
			$this->data_download = new Zend_Db_Expr('now()');
			$this->save();

			return $this->status;
		} catch (Exception $e) {
			throw $e;
		}
	}

	/**
	 * Processes survey of type survey
	 *
	 * @param array $data
	 * @return array
	 */
/* Function not in use? -- Hamza
	private function _processSurveyTypeSurvey($data)
	{
		//set vars for the _postProcessSurveyAction function
		$data["status"] = "completed";
		$data["processing_status"] = "pending";
		$data["downloaded"] = NULL;

		//set survey type survey response data
		$response["facebookCallbackUrl"] = "https://".BASE_DOMAIN."/starbar/content/facebook-post-result?shared_type=survey&shared_id=".$data["survey_id"]."&user_id=".$data["user_id"]."&user_key=".$data["user_key"]."&starbar_id=".$data["starbar_id"];

		$response["pixel_iframe_url"] = $this->_getPixelIframeUrl($data["user_id"]);

		//post process survey - set status, run game txn
		$this->_postProcessSurveyResponse($data);

		return $response;
	}
*/
	/**
	 * Performs common survey processing functionality.
	 *
	 * <p>Update status</p>
	 * <p>Run Game transaction</p>
	 *
	 * @param array $data
	 */
		private function _postProcessSurveyResponse($data)
		{
		}


/**
 * Right now this just checks for federated, but can be extended
 * to get any thrid party call back url based on domain logic.
 *
 * @param int $userId
 */
	private function _getPixelIframeUrl($userId)
	{
		$user = new User();
		$user->loadData($userId);

		// Set to http://www.samplicio.us/router2/ClientCallBack.aspx?fedResponseStatus=10&fedResponseID=xxxxx
		// for federated users who have completed a federated survey (note fedResponseStatus = 10)
		if ($user->federated_id && $this->getSurvey()->is_federated) {
			return "http://www.samplicio.us/router2/ClientCallBack.aspx?fedResponseStatus=10&fedResponseID=".$user->federated_id;
		} else {
			return "";
		}
	}
}
