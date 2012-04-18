<?php

class Survey extends Record
{
	protected $_tableName = 'survey';

	public static function getNextSurveyForUser($startSurvey, $userId) {
		// Figure out what the status of this survey is for this user
		$surveyResponse = new Survey_Response();
		$surveyResponse->loadDataByUniqueFields(array('survey_id' => $startSurvey->id, 'user_id' => $userId));
		if ($surveyResponse->status) {
			$surveyUserStatus = $surveyResponse->status;
		} else {
			$surveyUserStatus = 'new';
		}

		if ($surveyUserStatus == 'new' || $surveyUserStatus == 'archived') {
			$surveys = new SurveyCollection();
			$surveys->loadSurveysForStarbarAndUser($startSurvey->starbar_id, $userId, 'survey', $surveyUserStatus);
			$returnNextSurvey = false;
			foreach($surveys as $survey) {
				if ($returnNextSurvey) return $survey;
				if ($survey->id == $startSurvey->id) $returnNextSurvey = true;
			}
		}
		return new Survey();
	}

	public function getArrayOfUsersWhoResponded($commaDelimitedUserIdFilterList = null) {
		if (!$this->id) return;

		$sql = "SELECT user_id FROM survey_response WHERE survey_id = ? AND processing_status = 'completed'";

		if ($commaDelimitedUserIdFilterList) {
			// add to $sql
			$sql .= " AND user_id IN (" . trimCommas($commaDelimitedUserIdFilterList) . ")";
		}

		return Db_Pdo::fetchColumn($sql, $this->id);
	}

	public function afterInsert() {
		if (!$this->id) return;

		$messages = $this->retrieveQuestionsAndChoicesFromSurveyGizmo();
		if (sizeof($messages)) {
			$messages = array_merge(array("Survey/Poll questions and choices retrieved after survey->insert"), $messages);
			$message = implode("\n", $messages);
			quickLog($message);
		}
	}

	public function retrieveQuestionsAndChoicesFromSurveyGizmo() {
		$config = Api_Registry::getConfig();

		$decodedJson = false;
		$questionArray = array();
		$questionExternalIdReferenceArray = array();

		// Messages to show on interface after processing
		$messages = array();

		$surveyQuestionsSaved = 0;
		$surveyQuestionChoicesSaved = 0;

		if ($this->id && $this->external_id && $this->processing_status == "pending") {
			$sgUser = $config->surveyGizmo->api->username;
			$sgPass = $config->surveyGizmo->api->password;

			$requestParams["user:pass"] = $sgUser . ":" . $sgPass;

			$requestParamString = "";
			foreach ($requestParams as $key => $value) {
				if ($requestParamString) $requestParamString .= "&";
				else $requestParamString = "?";

				$requestParamString .= $key . "=" . $value;
			}

			$url = "https://restapi.surveygizmo.com/v1/survey/" . $this->external_id . "/surveyquestion" . $requestParamString;
			$messages[] = "Connecting to " . $url;

			$handle = fopen($url, 'r');
			set_time_limit(180); // Allow SG 3 minutes to respond

			$json = stream_get_contents($handle);
			if ($json) {
				$decodedJson = json_decode($json, true);
			} else {
				$decodedJson = null;
				throw new Api_Exception(Api_Error::create(Api_Error::SURVEYGIZMO_ERROR, 'Attempt to retreive survey responses failed when accessing: ' . $url));
			}
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
			$messages[] = "Survey Gizmo reports " . $decodedJson['total_count'] . " questions on " . $decodedJson['total_pages'] . " page(s)";
			$messages[] = "(including action and logic questions, which we don't save, and piped questions which we save as multiple questions)";

			$questionOrdinal = 1;
			$questionsData = $decodedJson['data'];

			for ($indexInQuestionArray = 0; $indexInQuestionArray < count($questionsData); $indexInQuestionArray++) {
				if (isset($questionsData[$indexInQuestionArray]['id']) && ((int) $questionsData[$indexInQuestionArray]['id'])) {
					$questionExternalIdReferenceArray[(int) $questionsData[$indexInQuestionArray]['id']] = $indexInQuestionArray;
				}
			}


			for ($mainQuestionCounter = 0; $mainQuestionCounter < count($questionsData); $mainQuestionCounter++) {
				// Reset time limit to allow for processing, allow for 60 seconds per question
				set_time_limit(60);

				$questionData = $questionsData[$mainQuestionCounter];

				$questionType = strtolower($questionData['_subtype']);
				$needToSaveQuestionAgain = false;

				// Skip disabled questions
				if (isset($questionData['properties']['disabled']) && $questionData['properties']['disabled']) {
					continue;
				}

				// Piped question, single choice from many
				if ($questionType == "table" && isset($questionData['properties']['piped_from']) && $questionData['properties']['piped_from']) {

					$pipedFrom = (int) $questionData['properties']['piped_from'];
					// The question that was piped from should have already been processed, and therefore should exist in the
					// $questionArray that we create as we process the questions from SG
					if ($pipedFrom && isset($questionArray[$pipedFrom]) && isset($questionArray[$pipedFrom]->option_array)) {
						$pipedFromQuestion = $questionArray[$pipedFrom];

						$pipedQuestion = new Survey_Question();
						$pipedQuestion->survey_id = $this->id;
						$pipedQuestion->choice_type = 'none';
						$pipedQuestion->data_type = 'none';
						$pipedQuestion->option_array = array();
						$pipedQuestion->piped_from_survey_question_id = $pipedFromQuestion->id; // id in local DB
						$pipedQuestion->title = $questionData['title']['English'];
						$pipedQuestion->external_question_id = (int) $questionData['id'];
						$pipedQuestion->number_of_choices = count($questionData['options']);
						$pipedQuestion->ordinal = $questionOrdinal * 10;
						$questionOrdinal++;

						$pipedQuestion->save();
						$surveyQuestionsSaved++;

						$choiceOrdinal = 1;

						foreach ($questionData['options'] as $optionData) {
							if ($optionData['_type'] == "SurveyOption" && !(isset($optionData['properties']['disabled']) && $optionData['properties']['disabled'])) {
								$questionChoice = new Survey_QuestionChoice();
								$questionChoice->survey_question_id = $pipedQuestion->id;
								$questionChoice->external_choice_id = (int) $optionData['id'];
								$questionChoice->title = $optionData['title']['English'];
								$questionChoice->value = $optionData['value'];
								$questionChoice->ordinal = $choiceOrdinal * 10;
								$choiceOrdinal++;

								$questionChoice->save();
								$surveyQuestionChoicesSaved++;

								$pipedQuestion->option_array[$questionChoice->external_choice_id] = $questionChoice;
							}
						}

						foreach($pipedFromQuestion->option_array as $pipedOption) { // Each option in the original question is a new question
							$question = new Survey_Question();
							$question->survey_id = $this->id;
							$question->choice_type = 'single';
							$question->data_type = 'none';
							$question->option_array = array();
							$question->piped_from_survey_question_id = $pipedQuestion->id; // id in local DB
							$question->piped_from_survey_question_choice_id = $pipedOption->id; // id in local DB
							$question->title = $pipedQuestion->title . " : " . $pipedOption->title;
							$question->external_question_id = $pipedQuestion->external_question_id;
							$question->external_pipe_choice_id = $pipedOption->external_choice_id;
							$question->number_of_choices = count($pipedQuestion->option_array);
							$question->ordinal = $questionOrdinal * 10;
							$questionOrdinal++;

							$question->save();
							$surveyQuestionsSaved++;

							// We could duplicate the options for each piped question
							// (that's what commented code block below does), but instead
							// we'll re-use the options from the original question since
							// they should be the same for all piped questions

							/*$choiceOrdinal = 1;
							foreach ($questionData['options'] as $optionData) {
								if ($optionData['_type'] == "SurveyOption") {
									$questionChoice = new Survey_QuestionChoice();
									$questionChoice->survey_question_id = $question->id;
									$questionChoice->external_choice_id = (int) $optionData['id'];
									$questionChoice->title = $optionData['title']['English'];
									$questionChoice->value = $optionData['value'];
									$questionChoice->ordinal = $choiceOrdinal * 10;
									$choiceOrdinal++;

									$questionChoice->save();
									$surveyQuestionChoicesSaved++;

									$question->option_array[$questionChoice->external_choice_id] = $questionChoice;
								}
							}
							*/

							$questionArray[$question->external_pipe_choice_id] = $question; // Add to array so we can easily find later for piping
						}

						$questionArray[$pipedQuestion->external_question_id] = $pipedQuestion; // Add to array so we can easily find later for piping
					}

				// Piped question, text value (string by default)
				} elseif ($questionType == "textbox" && isset($questionData['properties']['piped_from']) && $questionData['properties']['piped_from']) {

					$pipedFrom = (int) $questionData['properties']['piped_from'];
					if ($pipedFrom && isset($questionArray[$pipedFrom]) && isset($questionArray[$pipedFrom]->option_array)) {
						$pipedFromQuestion = $questionArray[$pipedFrom];

						foreach($pipedFromQuestion->option_array as $pipedOption) { // Each option in the original question is a new question
							$question = new Survey_Question();
							$question->survey_id = $this->id;
							$question->choice_type = 'none';
							$question->data_type = 'string';
							$question->option_array = array();
							$question->piped_from_survey_question_id = $pipedQuestion->id; // id in local DB
							$question->piped_from_survey_question_choice_id = $pipedOption->id; // id in local DB
							if (isset($questionData['title']['English']) && $questionData['title']['English']) {
								if (strpos($questionData['title']['English'], "[%%PIPED_VALUE%%]")) {
									$question->title = str_replace("[%%PIPED_VALUE%%]", $pipedOption->title, $questionData['title']['English']);
								} else {
									$question->title = $questionData['title']['English'] . " : " . $pipedOption->title;
								}
							} else {
								$question->title = $pipedOption->title;
							}
							$question->external_question_id = (int) $questionData['id'];
							$question->external_pipe_choice_id = $pipedOption->external_choice_id;
							$question->ordinal = $questionOrdinal * 10;
							$questionOrdinal++;

							$question->save();
							$surveyQuestionsSaved++;

							$questionArray[$question->external_pipe_choice_id] = $question; // Add to array so we can easily find later for piping
						}
					}

				// Piped question, single choice from multiple
				} elseif ($questionType == "radio" && isset($questionData['properties']['piped_from']) && $questionData['properties']['piped_from']) {

					$pipedFrom = (int) $questionData['properties']['piped_from'];
					if ($pipedFrom && isset($questionArray[$pipedFrom]) && isset($questionArray[$pipedFrom]->option_array)) {
						$pipedFromQuestion = $questionArray[$pipedFrom];

						$question = new Survey_Question();
						$question->survey_id = $this->id;
						$question->choice_type = 'single';
						$question->data_type = 'none';
						$question->piped_from_survey_question_id = $pipedFromQuestion->id; // id in local DB
						$question->title = $questionData['title']['English'];
						$question->external_question_id = (int) $questionData['id'];
						$question->ordinal = $questionOrdinal * 10;
						$questionOrdinal++;

						$question->save();
						$surveyQuestionsSaved++;

						// Options are in the piped-from question, so no need to re-add them

						$questionArray[$question->external_question_id] = $question; // Add to array so we can easily find later for piping
					}

				// Piped question, multiple choice from multiple
				} elseif ($questionType == "checkbox" && isset($questionData['properties']['piped_from']) && $questionData['properties']['piped_from']) {

					$pipedFrom = (int) $questionData['properties']['piped_from'];
					if ($pipedFrom && isset($questionArray[$pipedFrom]) && isset($questionArray[$pipedFrom]->option_array)) {
						$pipedFromQuestion = $questionArray[$pipedFrom];

						$question = new Survey_Question();
						$question->survey_id = $this->id;
						$question->choice_type = 'multiple';
						$question->data_type = 'none';
						$question->piped_from_survey_question_id = $pipedFromQuestion->id; // id in local DB
						$question->title = $questionData['title']['English'];
						$question->external_question_id = (int) $questionData['id'];
						$question->ordinal = $questionOrdinal * 10;
						$questionOrdinal++;

						$question->save();
						$surveyQuestionsSaved++;

						// Options are in the piped-from question, so no need to re-add them

						$questionArray[$question->external_question_id] = $question; // Add to array so we can easily find later for piping
					}

				// Parent question that pipes into several sub-questions (by default, they don't have the piped_from property set, so we'll fake it)
				} elseif ($questionType == "table" && isset($questionData['sub_question_skus']) && $questionData['sub_question_skus'] && count($questionData['sub_question_skus'])) { // Not a piped question, but still a table -- look for sub_question_skus

					$masterQuestionExternalId = (int) $questionData['id'];
					$masterQuestionTitle = (isset($questionData['title']['English']) ? $questionData['title']['English'] : "");

					$question = new Survey_Question();
					$question->survey_id = $this->id;
					$question->choice_type = 'none';
					$question->data_type = 'none';
					$question->option_array = array();
					$question->title = $questionData['title']['English'];
					$question->external_question_id = (int) $questionData['id'];
					$question->number_of_choices = count($questionData['options']);
					$question->ordinal = $questionOrdinal * 10;
					$questionOrdinal++;

					$question->save();
					$surveyQuestionsSaved++;

					$choiceOrdinal = 1;

					foreach ($questionData['options'] as $optionData) {
						if ($optionData['_type'] == "SurveyOption" && !(isset($optionData['properties']['disabled']) && $optionData['properties']['disabled'])) {
							$questionChoice = new Survey_QuestionChoice();
							$questionChoice->survey_question_id = $question->id;
							$questionChoice->external_choice_id = (int) $optionData['id'];
							$questionChoice->title = $optionData['title']['English'];
							$questionChoice->value = $optionData['value'];
							$questionChoice->ordinal = $choiceOrdinal * 10;
							$choiceOrdinal++;

							$questionChoice->save();
							$surveyQuestionChoicesSaved++;

							$question->option_array[$questionChoice->external_choice_id] = $questionChoice;
						}
					}

					$questionArray[$question->external_question_id] = $question; // Add to array so we can easily find later for piping

					foreach($questionData['sub_question_skus'] as $subQuestionId) { // for each sub_question
						$indexInQuestionArray = $questionExternalIdReferenceArray[(int) $subQuestionId];

						if (isset($questionsData[$indexInQuestionArray]['id']) && ((int) $questionsData[$indexInQuestionArray]['id']) == ((int) $subQuestionId)) {
							if (!isset($questionsData[$indexInQuestionArray]['title'])) $questionsData[$indexInQuestionArray]['title'] = array('English' => "");
							if (!isset($questionsData[$indexInQuestionArray]['title']['English'])) $questionsData[$indexInQuestionArray]['title']['English'] = "";
							$questionsData[$indexInQuestionArray]['title']['English'] = $masterQuestionTitle . ": " . $questionsData[$indexInQuestionArray]['title']['English'];

							if (!isset($questionsData[$indexInQuestionArray]['properties'])) $questionsData[$indexInQuestionArray]['properties'] = array();
							if (!isset($questionsData[$indexInQuestionArray]['properties']['piped_from'])) $questionsData[$indexInQuestionArray]['properties']['piped_from'] = "0";
							$questionsData[$indexInQuestionArray]['properties']['piped_from'] = $masterQuestionExternalId . "";
						}
					}

				// Question made up of several options that each has a textbox, so treat each one like a unique question. Also save the original question for reference/grouping
				// (each sub-question title is made up of the original question title + option title)
				} elseif ($questionType == "multi_textbox") {

					$masterQuestion = new Survey_Question();
					$masterQuestion->survey_id = $this->id;
					$masterQuestion->choice_type = 'none';
					$masterQuestion->data_type = 'none';
					$masterQuestion->external_question_id = (int) $questionData['id'];
					$masterQuestion->title = $questionData['title']['English'];
					$masterQuestion->ordinal = $questionOrdinal * 10;
					$questionOrdinal++;

					$masterQuestion->save();
					$surveyQuestionsSaved++;

					$questionArray[$masterQuestion->external_question_id] = $masterQuestion; // Add to array so we can easily find later for piping

					foreach ($questionData['options'] as $optionData) { // Each option in the original question is a new question
						$question = new Survey_Question();
						$question->survey_id = $this->id;
						$question->choice_type = 'none';
						$question->data_type = 'string';
						$question->piped_from_survey_question_id = $masterQuestion->id; // id in local DB
						$question->title = $masterQuestion->title . " : " . $optionData['title']['English'];
						$question->external_question_id = (int) $questionData['id'];
						$question->external_pipe_choice_id = (int) $optionData['id'];;
						$question->ordinal = $questionOrdinal * 10;
						$questionOrdinal++;

						$question->save();
						$surveyQuestionsSaved++;
					}

				// Non-piped, non-table questions (i.e. all other questions, except 'logic' questions and 'action' questions, which aren't really questions)
				} elseif (in_array($questionType, array("checkbox", "menu", "radio", "textbox", "rank"))) {

					$question = new Survey_Question();
					$question->survey_id = $this->id;
					$question->external_question_id = (int) $questionData['id'];
					$question->title = $questionData['title']['English'];
					$question->ordinal = $questionOrdinal * 10;
					$questionOrdinal++;

					switch (strtolower($questionData['_subtype'])) {
						case "checkbox":
							$question->choice_type = 'multiple';
							$question->data_type = 'none';
							$question->option_array = array();
							break;
						case "menu":
						case "radio":
							$question->choice_type = 'single';
							$question->data_type = 'none';
							$question->option_array = array();
							break;
						case "textbox":
							$question->choice_type = 'none';
							$question->data_type = 'string'; // Default to string
							break;
						case "rank":
							$question->choice_type = 'multiple';
							$question->data_type = 'integer';
							$question->option_array = array();
							break;
						default:
							break;
					}

					$question->number_of_choices = (isset($questionData['options']) ? count($questionData['options']) : 0);
					$question->save(); // save so we have the id available
					$surveyQuestionsSaved++;

					if ($question->number_of_choices) {
						$choiceOrdinal = 1;
						foreach ($questionData['options'] as $optionData) {
							if ($optionData['_type'] == "SurveyOption" && !(isset($optionData['properties']['disabled']) && $optionData['properties']['disabled'])) {
								$questionChoice = new Survey_QuestionChoice();
								$questionChoice->survey_question_id = $question->id;
								$questionChoice->external_choice_id = (int) $optionData['id'];
								$questionChoice->title = $optionData['title']['English'];
								$questionChoice->value = $optionData['value'];
								$questionChoice->ordinal = $choiceOrdinal * 10;
								$choiceOrdinal++;

								if (isset($optionData['properties']['other']) && $optionData['properties']['other'] && $question->data_type == 'none') {
									$questionChoice->other = true;
									$question->data_type = 'string'; // question is saved again below
									$needToSaveQuestionAgain = true;
								}

								$questionChoice->save();
								$surveyQuestionChoicesSaved++;

								$question->option_array[$questionChoice->external_choice_id] = $questionChoice;
							}
						}
					}

					if ($needToSaveQuestionAgain) $question->save();

					$questionArray[$question->external_question_id] = $question; // Add to array so we can easily find later for piping
				}

			} // End of main loop through questions

			if ($questionOrdinal - 1) {
				$this->number_of_questions = $questionOrdinal - 1;
				if (!$this->display_number_of_questions) $this->display_number_of_questions = $this->number_of_questions . "";
			}
			if ($this->type == "poll") {
				$this->number_of_answers = $surveyQuestionChoicesSaved;
			}

			$this->processing_status = "completed";
			$this->save();

			$messages[] = "";
			$messages[] = "Processing Complete!";
			$messages[] = "survey_question records saved in the DB: " . $surveyQuestionsSaved;
			$messages[] = "survey_question_choice records saved in DB: " . $surveyQuestionChoicesSaved;

		}

		return $messages;
	}

	// This function is deprecated -- relies on bundle_of_joy variable which is no longer in use.
	// See SurveyResponse::process() for new version, which is not a batch function,
	// instead it is intended to be used for live collection of responses (one by one)
	/*
	public function retrieveBatchResponsesFromSurveyGizmo () {
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

		$currentPage = 1;
		$totalNumberOfPages = 1;

		if ($this->id && $this->external_id) {
			$sgUser = $config->surveyGizmo->api->username;
			$sgPass = $config->surveyGizmo->api->password;

			$requestParams["user:pass"] = $sgUser . ":" . $sgPass;
			$requestParams["resultsperpage"] = 25;

			// For more on SG filters: http://developer.surveygizmo.com/resources/filtering-and-browsing-results/
			// $requestParams["filter[field][0]"] = "status";
			// $requestParams["filter[operator][0]"] = "="; // Can also use "!=" here
			// $requestParams["filter[value][0]"] = "Complete"; // Can also use "Deleted" here

			while ($currentPage <= $totalNumberOfPages) {
				$requestParams["page"] = $currentPage;

				$requestParamString = "";
				foreach ($requestParams as $key => $value) {
					if ($requestParamString) $requestParamString .= "&";
					else $requestParamString = "?";

					$requestParamString .= $key . "=" . $value;
				}

				$url = "https://restapi.surveygizmo.com/v1/survey/" . $this->external_id . "/surveyresponse" . $requestParamString;
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
					// Initialize reference arrays so we don't have to repeatedly call the DB
					if ($currentPage == 1) {
						$messages[] = "Survey Gizmo reports " . $decodedJson['total_count'] . " responses on " . $decodedJson['total_pages'] . " pages";

						$totalNumberOfPages = (int) $decodedJson['total_pages'];

						// for testing:
						// if ($totalNumberOfPages > 2) $totalNumberOfPages = 2;

						$allSurveyQuestions = new Survey_QuestionCollection();
						$allSurveyQuestions->loadAllQuestionsForSurvey($this->id);

						$allSurveyQuestionChoices = new Survey_QuestionChoiceCollection();
						$allSurveyQuestionChoices->loadAllChoicesForSurvey($this->id);

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
					}

					// All the responses on this page of results
					$responsesData = $decodedJson['data'];

					// Go through all the responses on this page (should be one per user)
					foreach ($responsesData as $responseData) {
						// Reset time limit to allow for processing, allow for 60 seconds per response
						set_time_limit(60);
						$externalResponseId = 0;
						$userId = 0;
						$dataToSave = array();

						// Go through this responses's answers (i.e. all the answers one user gave)
						foreach ($responseData as $answerKey => $answerValue) {
							if ($answerKey == "id") {
								$externalResponseId = (int) $answerValue;
							} elseif ($answerKey == "status") {
								if ($answerValue == "Complete" || $answerValue == "Disqualified") {
									continue;
								} else {
									// Skip Partial (or other?) responses
									$externalResponseId = 0;
									$userId = 0;
									$dataToSave = array();
									break;
								}
							// Look for the user_id in the bundle_of_joy, which starts like this: "user_id^-^123^|^..."
							} elseif (strpos($answerValue, "user_id^-^") !== false) {
								$userId = (int) substr($answerValue, 10, strpos($answerValue, "^|^")-10);
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
						if ($externalResponseId && $userId && count($dataToSave)) {
							$surveyResponse = new Survey_Response();
							$surveyResponse->loadDataByUniqueFields(array("user_id" => $userId, "survey_id" => $this->id, "processing_status" => "pending"));

							if ($surveyResponse->id) {
								foreach ($dataToSave as $dataKey => $surveyQuestionResponseData) {
									$surveyQuestionResponse = new Survey_QuestionResponse();
									$surveyQuestionResponse->survey_response_id = $surveyResponse->id;
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

								$surveyResponse->data_download = new Zend_Db_Expr('now()');
								$surveyResponse->processing_status = "completed";
								$surveyResponse->save();
							}
						} // data for this response (i.e. all the answers for one user) done being saved!
					} // end of processing for each response (i.e. 1 per user)

					// At this point, we have processed all the responses on this page

					// Fetch next page of results
					$currentPage++;
				}
			} // Done going through all pages of results

			$messages[] = "";
			$messages[] = "Processing Complete!";
			$messages[] = "Rows matching one of the regular expressions: " . $rowsMatchingRegex;
			$messages[] = "survey_question_response records saved in DB: " . $surveyQuestionResponsesSaved;
			$messages[] = "Note that rows saved is usually less than rows matching, since some data is repeated in SG's response";
			$messages[] = "Rows not matching any of the regular expressions (non-zero expected): " . $rowsMatchingNoRegex;

			return $messages;
		}
	}
	*/
}
