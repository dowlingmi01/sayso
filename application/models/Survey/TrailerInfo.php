<?php

class Survey_TrailerInfo extends Record
{
	protected $_tableName = 'survey_trailer_info';

	public function loadDataBySurveyId($surveyId) {
		$this->loadDataByUniqueFields(array('survey_id' => $surveyId));
	}

	public function afterInsert() {

		if ($this->trailer_template_id != "") {

			// Get the survey questions for the trailer
			$surveyQuestions = new Survey_QuestionCollection();
			$surveyQuestions->loadAllQuestionsForSurvey($this->trailer_template_id);
			$questionCtr = 0;
			foreach ($surveyQuestions as $surveyQuestion) {

				$questionCtr++;
				$question = new Survey_Question();
				$question->loadData($surveyQuestion->id);
				$question->id = null; // Treats this as an Insert instead of an Edit
				$question->survey_id = $this->survey_id;
				$question->survey_trailer_info_id = $this->id;
				$question->external_question_id = null;
				$question->ordinal = $questionCtr;
				$question->save();
				$newQuestionID = $question->id;

				// Build the question choices for this survey question
				$surveyQuestionChoices = new Survey_QuestionChoiceCollection();
				$surveyQuestionChoices->loadAllChoicesForSurveyQuestion($surveyQuestion->id);

				foreach ($surveyQuestionChoices as $surveyQuestionChoice) {

					$choice = new Survey_QuestionChoice();
					$choice->loadData($surveyQuestionChoice->id);
					$choice->id = null;
					$choice->survey_question_id = $newQuestionID;
					$choice->external_choice_id = null;
					$choice->save();
				}
			}
		}
	}
}
