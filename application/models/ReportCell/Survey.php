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

		// Now go through all the questions and numeric responses and calculate stuff!
		foreach ($surveyQuestions as $surveyQuestion) {
			$reportCellSurveyCalculation = new ReportCell_SurveyCalculation();
			$reportCellSurveyCalculation->report_cell_survey_id = $this->id;
			$reportCellSurveyCalculation->parent_type = "survey_question";
			$reportCellSurveyCalculation->survey_question_id = $surveyQuestion->id;

			$reportCellSurveyCalculation->comma_delimited_list_of_users = ',' . $surveyQuestion->getStringOfUsersWhoAnsweredThisQuestion($reportCell->comma_delimited_list_of_users) . ',';
			if ($reportCellSurveyCalculation->comma_delimited_list_of_users == ",,") {
				$reportCellSurveyCalculation->comma_delimited_list_of_users = "";
				$reportCellSurveyCalculation->number_of_responses = 0;
			} else {
				$reportCellSurveyCalculation->number_of_responses = substr_count($reportCellSurveyCalculation->comma_delimited_list_of_users, ',') - 1;
			}

			if ($surveyQuestion->data_type == 'integer' || $surveyQuestion->data_type == 'decimal' || $surveyQuestion->data_type == 'monetary') {
				$reportCellSurveyCalculation->average = $surveyQuestion->getAverage($reportCell->comma_delimited_list_of_users);
				$reportCellSurveyCalculation->stardard_deviation = $surveyQuestion->getStandardDeviation($reportCell->comma_delimited_list_of_users);
				$reportCellSurveyCalculation->median = $surveyQuestion->getMedian($reportCell->comma_delimited_list_of_users);
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

					$reportCellSurveyCalculation->comma_delimited_list_of_users = ',' . $surveyQuestionChoice->getStringOfUsersWhoChoseThisChoice($sharingQuestionId, $reportCell->comma_delimited_list_of_users) . ',';
					if ($reportCellSurveyCalculation->comma_delimited_list_of_users == ",,") {
						$reportCellSurveyCalculation->comma_delimited_list_of_users = "";
						$reportCellSurveyCalculation->number_of_responses = 0;
					} else {
						$reportCellSurveyCalculation->number_of_responses = substr_count($reportCellSurveyCalculation->comma_delimited_list_of_users, ',') - 1;
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

				$reportCellSurveyCalculation->comma_delimited_list_of_users = ',' . $surveyQuestionChoice->getStringOfUsersWhoChoseThisChoice($surveyQuestionChoice->survey_question_id, $reportCell->comma_delimited_list_of_users) . ',';
				if ($reportCellSurveyCalculation->comma_delimited_list_of_users == ",,") {
					$reportCellSurveyCalculation->comma_delimited_list_of_users = "";
					$reportCellSurveyCalculation->number_of_responses = 0;
				} else {
					$reportCellSurveyCalculation->number_of_responses = substr_count($reportCellSurveyCalculation->comma_delimited_list_of_users, ',') - 1;
				}

				$reportCellSurveyCalculation->save();
			}
		}

		$this->last_processed = new Zend_Db_Expr('now()');
		$this->save();
	}
}
