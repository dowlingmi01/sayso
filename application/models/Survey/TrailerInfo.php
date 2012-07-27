<?php

class Survey_TrailerInfo extends Record
{
	protected $_tableName = 'survey_trailer_info';

	public function loadDataBySurveyId($surveyId) {
		$this->loadDataByUniqueFields(array('survey_id' => $surveyId));
	}

	public function afterInsert() {
		$choiceTitles = array();

		switch($this->category) {
			case "retro movie":
				$question1Title = "Did the trailer match your expectations for the movie?";
				$question2Title = "How many thumbs up would you give the trailer for " . $this->entertainment_title . " (not the movie)?";
				$choiceTitles[] = "The trailer was better than the movie.";
				$choiceTitles[] = "The trailer was equal to the movie.";
				$choiceTitles[] = "The trailer was worse than the movie.";
				$choiceTitles[] = "I haven't seen the movie.";
				break;
			case "game":
				$question1Title = "Rate this pre-release game trailer.";
				$question2Title = "Did the trailer impact your decision to want to buy or play this game?";
				$choiceTitles[] = "After seeing the trailer, I won't buy or play the game.";
				$choiceTitles[] = "After seeing the trailer, I'm definitely interested in playing the game, but I won't buy it.";
				$choiceTitles[] = "After seeing the trailer, I'll definitely buy and play the game.";
				$choiceTitles[] = "The trailer didn't affect my decision to play or buy the game.";
				break;
			default:
				return;
				break;
		}

		$surveyQuestion = new Survey_Question();
		$surveyQuestion->survey_id = $this->survey_id;
		$surveyQuestion->data_type = "none";
		$surveyQuestion->choice_type = "single";
		$surveyQuestion->title = $question1Title;
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
		$surveyQuestion->title = $question2Title;
		$surveyQuestion->ordinal = 2;
		$surveyQuestion->number_of_choices = count($choiceTitles);
		$surveyQuestion->save();

		if ($surveyQuestion->id) {
			foreach ($choiceTitles as $i => $choiceTitle) {
				$surveyQuestionChoice = new Survey_QuestionChoice();
				$surveyQuestionChoice->survey_question_id = $surveyQuestion->id;
				$surveyQuestionChoice->title = $choiceTitle;
				$surveyQuestionChoice->value = $choiceTitle;
				$surveyQuestionChoice->ordinal = $i;
				$surveyQuestionChoice->save();
			}
		}
	}
}
