<?php

class Survey_TrailerInfo extends Record
{
	protected $_tableName = 'survey_trailer_info';

	public function loadDataBySurveyId($surveyId) {
		$this->loadDataByUniqueFields(array('survey_id' => $surveyId));
	}

	public function afterInsert() {

		if ($this->wraparound_id != "") {

			// Get the survey questions
			$surveyQuestions = new Survey_QuestionCollection();
			$surveyQuestions->loadAllQuestionsForSurvey($this->wraparound_id);

			$questionCtr = 0;
			foreach ($surveyQuestions as $surveyQuestion) {

				$questionCtr++;
				$question = new Survey_Question();
				$question->loadData($surveyQuestion->id);
				$question->id = null; // Treats this as an Insert instead of an Edit
				$question->survey_id = $this->survey_id;
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
	/**
	* Called after a Survey_TrailerInfo record is written to the database for the first time.
	*
	*
	*/
	public function afterInsertOld() {
		// Set survey_id
DebugBreak('1;d=1');
	//	$this->getRequest()->setParam('survey_id',1025);// 1025 is the default survey for a retro trailer
	//	$this->_forward('embedPoll', 'Machinima','starbar');

	// Get the specified wrap around, if there is one
	if ($this->wraparound_id != "") {
		//$wraparound_table = new Survey();
		//$wraparound = $wraparound_table->fetchRow($wraparound_table->select()->where('id = ?',$this->wraparound_id));
		$surveyQuestionChoices = new Survey_QuestionChoiceCollection();
				$surveyQuestionChoices->loadAllChoicesForSurvey($this->wraparound_id);

				foreach ($surveyQuestionChoices as $surveyQuestionChoice) {
					$surveyQuestions[$surveyQuestionChoice->survey_question_id]->option_array[$surveyQuestionChoice->id] = $surveyQuestionChoice;
				}
	}

	// No specified wrap-around. Find the default wrap-around for this type
	$choiceTitles = array();

		switch($this->category) {
			case "retro movie":

				$question1Title = "PTC Did the trailer match your expectations for the movie?";
				$question2Title = "How many thumbs up would you give the trailer for " . $this->entertainment_title . " (not the movie)?";
				$choiceTitles[] = "The trailer was better than the movie.";
				$choiceTitles[] = "The trailer was equal to the movie.";
				$choiceTitles[] = "The trailer was worse than the movie.";
				$choiceTitles[] = "I haven't seen the movie.";
				break;
			case "pre-release movie":
				$question1Title = "PTC Rate this pre-release movie trailer.";
				$question2Title = "Did the trailer affect your decision to want to see this movie in a theater?";
				$choiceTitles[] = "After seeing the trailer, I have no interest in seeing this movie in a theater or otherwise.";
				$choiceTitles[] = "The trailer didn't convince me to see it in a theater; but I'll wait and see it on DVD or stream it.";
				$choiceTitles[] = "The trailer didn't affect my decision one way or another to see the movie.";
				$choiceTitles[] = "The trailer made me more interested in seeing the movie in a theater.";
				break;
			case "pre-release game":
				$question1Title = "PTC Rate this pre-release game trailer.";
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
