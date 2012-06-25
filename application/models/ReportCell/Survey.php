<?php

class ReportCell_Survey extends Record
{
	protected $_tableName = 'report_cell_survey';

	public function process() {
		if (!$this->id || !$this->survey_id) return;

		$reportCell = new ReportCell();
		$reportCell->loadData($this->report_cell_id);

		if (!$reportCell->id) return;

		// Delete existing calculations, if any
		$reportCellSurveyCalculations = new ReportCell_SurveyCalculationCollection();
		$reportCellSurveyCalculations->deleteAllCalculationsForReportCellSurvey($this->id);

		// ---- Process Survey Questions ----
		$surveyQuestions = new Survey_QuestionCollection();
		$surveyQuestions->loadAllQuestionsForSurvey($this->survey_id);

		// Add response_array to survey questions
		foreach ($surveyQuestions as $questionId => $surveyQuestion) {
			if ($surveyQuestion->data_type == 'integer' || $surveyQuestion->data_type == 'decimal' || $surveyQuestion->data_type == 'monetary') {
				$surveyQuestions[$questionId]->loadAllResponses($reportCell->comma_delimited_list_of_users);
			}
		}

		// Now go through all the questions and numeric responses and calculate stuff!
		foreach ($surveyQuestions as $surveyQuestion) {
			$reportCellSurveyCalculation = new ReportCell_SurveyCalculation();
			$reportCellSurveyCalculation->report_cell_survey_id = $this->id;
			$reportCellSurveyCalculation->parent_type = "survey_question";
			$reportCellSurveyCalculation->survey_question_id = $surveyQuestion->id;

			$userArray = $surveyQuestion->getArrayOfUsersWhoAnsweredThisQuestion($reportCell->comma_delimited_list_of_users);
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
				foreach ($surveyQuestion->response_array as $responseValue) {
					$runningTotal += $responseValue;
					$numberOfResponses++;
				}
				$reportCellSurveyCalculation->average = $runningTotal / $numberOfResponses;
				$reportCellSurveyCalculation->median = $surveyQuestion->response_array[intval(floor(count($surveyQuestion->response_array)/2.0))];

				// Calculate the standard deviation using the average
				foreach ($surveyQuestion->response_array as $responseValue) {
					$deviation = $reportCellSurveyCalculation->average - $responseValue;
					$squareDeviationsTotal += $deviation * $deviation;
				}
				$reportCellSurveyCalculation->stardard_deviation = sqrt($squareDeviationsTotal / ($numberOfResponses - 1));
			}

			$reportCellSurveyCalculation->save();
		}



		// ---- Process Survey Question Choices ----
		$surveyQuestionChoices = new Survey_QuestionChoiceCollection();
		$surveyQuestionChoices->loadAllChoicesForSurvey($this->survey_id);

		foreach ($surveyQuestionChoices as $surveyQuestionChoice) {
			// Check if the choice is shared among several questions
			if ($surveyQuestions[$surveyQuestionChoice->survey_question_id]->choice_type == 'none') {
				// Choice is shared among more than one question
				$questionsThatShareChoiceArray = $surveyQuestionChoice->getArrayOfQuestionsThatShareThisChoice();
				foreach ($questionsThatShareChoiceArray as $sharingQuestionId) {
					$reportCellSurveyCalculation = new ReportCell_SurveyCalculation();
					$reportCellSurveyCalculation->report_cell_survey_id = $this->id;
					$reportCellSurveyCalculation->parent_type = "survey_question_choice";
					$reportCellSurveyCalculation->survey_question_id = $sharingQuestionId;
					$reportCellSurveyCalculation->survey_question_choice_id = $surveyQuestionChoice->id;

					$userArray = $surveyQuestionChoice->getArrayOfUsersWhoChoseThisChoice($sharingQuestionId, $reportCell->comma_delimited_list_of_users);
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

				$userArray = $surveyQuestionChoice->getArrayOfUsersWhoChoseThisChoice($surveyQuestionChoice->survey_question_id, $reportCell->comma_delimited_list_of_users);
				if (count($userArray)) {
					$reportCellSurveyCalculation->number_of_responses = count($userArray);
					$reportCellSurveyCalculation->comma_delimited_list_of_users = ',' . implode(',', $userArray) . ',';
				}

				$reportCellSurveyCalculation->save();
			}
		}

		$this->last_processed = new Zend_Db_Expr('now()');
		$this->save();
	}
}
