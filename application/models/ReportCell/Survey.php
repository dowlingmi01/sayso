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

			$reportCellSurveyCalculation->number_of_responses = $surveyQuestion->getCountOfUsersInReportCellWhoAnsweredThisQuestion($reportCell->id);

			if ($surveyQuestion->data_type == 'integer' || $surveyQuestion->data_type == 'decimal' || $surveyQuestion->data_type == 'monetary') {
				$reportCellSurveyCalculation->average = $surveyQuestion->getAverage($reportCell->id);
				$reportCellSurveyCalculation->stardard_deviation = $surveyQuestion->getStandardDeviation($reportCell->id);
				$reportCellSurveyCalculation->median = $surveyQuestion->getMedian($reportCell->id);
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

					$reportCellSurveyCalculation->number_of_responses = $surveyQuestionChoice->getCountOfUsersInReportCellWhoChoseThisChoice($sharingQuestionId, $reportCell->id);

					$reportCellSurveyCalculation->save();
				}
			} else {
				// Choice is not shared
				$reportCellSurveyCalculation = new ReportCell_SurveyCalculation();
				$reportCellSurveyCalculation->report_cell_survey_id = $this->id;
				$reportCellSurveyCalculation->parent_type = "survey_question_choice";
				$reportCellSurveyCalculation->survey_question_id = $surveyQuestionChoice->survey_question_id;
				$reportCellSurveyCalculation->survey_question_choice_id = $surveyQuestionChoice->id;

				$reportCellSurveyCalculation->number_of_responses = $surveyQuestionChoice->getCountOfUsersInReportCellWhoChoseThisChoice($surveyQuestionChoice->survey_question_id, $reportCell->id);
				$reportCellSurveyCalculation->save();
			}
		}

		$this->last_processed = new Zend_Db_Expr('now()');
		$this->save();
	}
}
