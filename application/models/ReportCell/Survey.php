<?php

class ReportCell_Survey extends Record
{
	protected $_tableName = 'report_cell_survey';

	public function process() {
		if (!$this->id || !$this->survey_id) return;

		// There should be no calculations already present, but just in case, run a delete
		$reportCellSurveyCalculations = new ReportCell_SurveyCalculationCollection();
		$reportCellSurveyCalculations->deleteAllCalculationsForReportCellSurvey($this->id);


		// ---- Process Survey Questions ----
		$surveyQuestions = new Survey_QuestionCollection();
		$surveyQuestions->loadAllQuestionsForSurvey($this->survey_id);
		$surveyQuestionArray = array();

		// Place survey questions into an array, where the key is the survey_question_id
		foreach ($surveyQuestions as $surveyQuestion) {
			$surveyQuestion->response_array = array();
			$surveyQuestionArray[$surveyQuestion->id] = $surveyQuestion;
		}

		$surveyQuestionResponses = new Survey_QuestionResponseCollection();
		$surveyQuestionResponses->loadAllNumericResponsesForSurvey($this->survey_id, $this->comma_delimited_list_of_users);

		// Add all the numeric responses to the question array
		foreach ($surveyQuestionResponses as $surveyQuestionResponse) {
			$surveyQuestionArray[$surveyQuestionResponse->survey_question_id]->response_array[] = $surveyQuestionResponse;
		}

		// Now go through all the questions and numeric responses and calculate stuff!
		foreach ($surveyQuestionArray as $surveyQuestion) {
			$reportCellSurveyCalculation = new ReportCell_SurveyCalculation();
			$reportCellSurveyCalculation->report_cell_survey_id = $this->id;
			$reportCellSurveyCalculation->parent_type = "survey_question";
			$reportCellSurveyCalculation->survey_question_id = $surveyQuestion->id;

			$userArray = $surveyQuestion->getArrayOfUsersWhoAnsweredThisQuestion($this->comma_delimited_list_of_users);
			if (count($userArray)) {
				$reportCellSurveyCalculation->number_of_responses = count($userArray);
				$reportCellSurveyCalculation->comma_delimited_list_of_users = ',' . implode(',', $userArray) . ',';
			}

			if (
				($surveyQuestion->data_type == 'integer' || $surveyQuestion->data_type == 'decimal' || $surveyQuestion->data_type == 'monetary')
				&& count($surveyQuestion->response_array)
			) {
				$runningTotal = 0.0;
				$squareDeviationsTotal = 0.0;
				$numberOfResponses = 0;

				// Calculate the total for the average
				foreach ($surveyQuestion->response_array as $surveyQuestionResponse) {
					$responseValue = ($surveyQuestion->data_type == 'integer' ? $surveyQuestionResponse->response_integer : $surveyQuestionResponse->response_decimal);
					$runningTotal += $responseValue;
					$numberOfResponses++;
				}
				$reportCellSurveyCalculation->average = $runningTotal / $numberOfResponses;
				$medianResponse = $surveyQuestion->response_array[intval(floor(count($surveyQuestion->response_array)/2.0))];
				$reportCellSurveyCalculation->median = ($surveyQuestion->data_type == 'integer' ? $medianResponse->response_integer : $medianResponse->response_decimal);

				// Calculate the standard deviation using the average
				foreach ($surveyQuestion->response_array as $surveyQuestionResponse) {
					$responseValue = ($surveyQuestion->data_type == 'integer' ? $surveyQuestionResponse->response_integer : $surveyQuestionResponse->response_decimal);
					$deviation = $reportCellSurveyCalculation->average - $responseValue;
					$squareDeviationsTotal += $deviation * $deviation;
				}
				$reportCellSurveyCalculation->stardard_deviation = sqrt($squareDeviationsTotal / ($numberOfResponses - 1));
			}

			$reportCellSurveyCalculation->save();
		}



		// ---- Process Survey Question Groups ----
		/*
		@ todo
		$surveyQuestionGroups = new Survey_QuestionGroupCollection();
		$surveyQuestionGroups->loadAllForSurvey($this->survey_id);
		*/



		// ---- Process Survey Question Choices ----
		$surveyQuestionChoices = new Survey_QuestionChoiceCollection();
		$surveyQuestionChoices->loadAllChoicesForSurvey($this->survey_id);

		foreach ($surveyQuestionChoices as $surveyQuestionChoice) {
			// Check if the choice is shared among several questions
			if ($surveyQuestionArray[$surveyQuestionChoice->survey_question_id]->choice_type == 'none') {
				// Choice is shared among more than one question
				$questionsThatShareChoiceArray = $surveyQuestionChoice->getArrayOfQuestionsThatShareThisChoice();
				foreach ($questionsThatShareChoiceArray as $sharingQuestionId) {
					$reportCellSurveyCalculation = new ReportCell_SurveyCalculation();
					$reportCellSurveyCalculation->report_cell_survey_id = $this->id;
					$reportCellSurveyCalculation->parent_type = "survey_question_choice";
					$reportCellSurveyCalculation->survey_question_id = $sharingQuestionId;
					$reportCellSurveyCalculation->survey_question_choice_id = $surveyQuestionChoice->id;

					$userArray = $surveyQuestionChoice->getArrayOfUsersWhoChoseThisChoice($sharingQuestionId, $this->comma_delimited_list_of_users);
					if (count($userArray)) {
						$reportCellSurveyCalculation->number_of_responses = count($userArray);
						$reportCellSurveyCalculation->comma_delimited_list_of_users = ',' . implode(',', $userArray) . ',';
					}

					$reportCellSurveyCalculation->save();
				}
			} else {
				// Choice is not shared
				$reportCellSurveyCalculation = new ReportCell_SurveyCalculation();
				$reportCellSurveyCalculation->report_cell_survey_id = $this->id;
				$reportCellSurveyCalculation->parent_type = "survey_question_choice";
				$reportCellSurveyCalculation->survey_question_id = $surveyQuestionChoice->survey_question_id;
				$reportCellSurveyCalculation->survey_question_choice_id = $surveyQuestionChoice->id;

				$userArray = $surveyQuestionChoice->getArrayOfUsersWhoChoseThisChoice($surveyQuestionChoice->survey_question_id, $this->comma_delimited_list_of_users);
				if (count($userArray)) {
					$reportCellSurveyCalculation->number_of_responses = count($userArray);
					$reportCellSurveyCalculation->comma_delimited_list_of_users = ',' . implode(',', $userArray) . ',';
				}

				$reportCellSurveyCalculation->save();
			}
		}



		// ---- Process Survey Question Choice Groups ----
		/*
		@ todo
		$surveyQuestionChoiceGroups = new Survey_QuestionChoiceGroupCollection();
		$surveyQuestionChoiceGroups->loadAllForSurvey($this->survey_id);
		*/
	}
}
