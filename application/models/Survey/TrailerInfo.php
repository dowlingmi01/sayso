<?php

class Survey_TrailerInfo extends Record
{
	protected $_tableName = 'survey_trailer_info';

	public function loadDataBySurveyId($surveyId) {
		$this->loadDataByUniqueFields(array('survey_id' => $surveyId));
	}

	public function afterInsert() {
		switch($this->category) {
			case "retro movie":
				$trailerForType = "movie";
				$notExperiencedChoice = "I haven't seen the movie";
				break;
			case "game":
				$trailerForType = "game";
				$notExperiencedChoice = "I haven't played the game";
				break;
			default:
				return;
				break;
		}

		$surveyQuestion = new Survey_Question();
		$surveyQuestion->survey_id = $this->survey_id;
		$surveyQuestion->data_type = "none";
		$surveyQuestion->choice_type = "single";
		$surveyQuestion->title = "How many thumbs up would you give the trailer for " . $this->entertainment_title . " (not the " . $trailerForType . ")?";
		$surveyQuestion->ordinal = 1;
		$surveyQuestion->number_of_choices = 5;
		$surveyQuestion->save();

		if ($surveyQuestion->id) {
			for ($i = 1; $i <= 5; $i++) {
				$surveyQuestionChoice = new Survey_QuestionChoice();
				$surveyQuestionChoice->survey_question_id = $surveyQuestion->id;
				$surveyQuestionChoice->title = "" . $i;
				$surveyQuestionChoice->value = "" . $i;
				$surveyQuestionChoice->ordinal = $i;
				$surveyQuestionChoice->save();
			}
		}

		$surveyQuestion = new Survey_Question();
		$surveyQuestion->survey_id = $this->survey_id;
		$surveyQuestion->data_type = "none";
		$surveyQuestion->choice_type = "single";
		$surveyQuestion->title = "Did the trailer match your expectations for the " . $trailerForType . "?";
		$surveyQuestion->ordinal = 2;
		$surveyQuestion->number_of_choices = 4;
		$surveyQuestion->save();

		if ($surveyQuestion->id) {
			$choices = array(
				1 => "The trailer was better than the " . $trailerForType,
				2 => "The trailer was equal to the " . $trailerForType,
				3 => "The trailer was worse than the " . $trailerForType,
				4 => $notExperiencedChoice,
			);
			foreach ($choices as $i => $choice) {
				$surveyQuestionChoice = new Survey_QuestionChoice();
				$surveyQuestionChoice->survey_question_id = $surveyQuestion->id;
				$surveyQuestionChoice->title = $choice;
				$surveyQuestionChoice->value = $choice;
				$surveyQuestionChoice->ordinal = $i;
				$surveyQuestionChoice->save();
			}
		}
	}
}
