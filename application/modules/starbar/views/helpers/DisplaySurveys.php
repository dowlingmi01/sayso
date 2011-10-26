<?php
class Starbar_View_Helper_DisplaySurveys extends Zend_View_Helper_Abstract
{
	function displaySurveys($status) {
		
		$numberOfPremiumNotes = 500;
		$numberOfPremiumChops = 5000;
		$numberOfRegularNotes = 50;
		$numberOfRegularChops = 500;

		$numberOfPremiumNotesDisqualified = 200;
		$numberOfPremiumChopsDisqualified = 2000;
		$numberOfRegularNotesDisqualified = 25;
		$numberOfRegularChopsDisqualified = 250;

		switch ($status) {
			case "new":
				$surveys = $this->view->new_surveys;
				$numberToShow= $this->view->count_new_surveys;
				break;

			case "completed":
				$surveys = $this->view->completed_surveys;
				$numberToShow= $this->view->count_completed_surveys;
				break;

			case "disqualified":
				$surveys = $this->view->disqualified_surveys;
				$numberToShow= $this->view->count_disqualified_surveys;
				break;

			case "archived":
				$surveys = $this->view->archived_surveys;
				$numberToShow= $this->view->count_archived_surveys;
				break;

			default:
				return;
		}

		if ($surveys) {
			$i = 0;
			foreach ($surveys as $survey) {
				// The numberToShow can be smaller than the size of the list
				if ($i >= $numberToShow) break;
				
				// "sb_listOdd" or "sb_listEven"
				$listItemClass = "sb_list".(($i % 2) ? "Odd" : "Even");

				if ($status == 'new' || $status == 'archived') {
					if ($survey->premium) {
						$strongClass = "sb_theme_textHighlight_alt";
						$buttonClass = "sb_theme_button sb_theme_button_alt";
						$popBoxToOpen = "sb_popBox_surveys_hg";
						$numberOfNotes = $numberOfPremiumNotes;
						$numberOfChops = $numberOfPremiumChops;
					} else {
						$strongClass = "sb_theme_textHighlight";
						$buttonClass = "sb_theme_button";
						$popBoxToOpen = "sb_popBox_surveys_lg";
						$numberOfNotes = $numberOfRegularNotes;
						$numberOfChops = $numberOfRegularChops;
					}
				} elseif ($status == 'completed') {
					if ($survey->premium) {
						$numberOfNotes = $numberOfPremiumNotesDisqualified;
						$numberOfChops = $numberOfPremiumChopsDisqualified;
					} else {
						$numberOfNotes = $numberOfRegularNotesDisqualified;
						$numberOfChops = $numberOfRegularChopsDisqualified;
					}
				} elseif ($status == 'disqualified') {
					if ($survey->premium) {
						$numberOfNotes = $numberOfPremiumNotesDisqualified;
						$numberOfChops = $numberOfPremiumChopsDisqualified;
					} else {
						$numberOfNotes = $numberOfRegularNotesDisqualified;
						$numberOfChops = $numberOfRegularChopsDisqualified;
					}
				}
				?>
				<? if ($status == 'new' || $status == 'archived') { // User can take this survey ?>
					<li class="<?= $listItemClass ?>">
						<div class="sb_surveyInfo">
							<h3><?= $survey->title ?></h3>
							<p><?= $survey->number_of_questions ?> Questions - Earn <strong class="<?= $strongClass ?>"><?= $numberOfNotes ?> Notes</strong> and <strong class="<?= $strongClass ?>"><?= $numberOfChops ?> Chops</strong></p>
						</div><!-- .sb_surveyInfo -->
						<a href="http://<?= BASE_DOMAIN ?>/starbar/hellomusic/embed-survey?survey_id=<?= $survey->id ?>" class="sb_surveyLaunch <?= $buttonClass ?> sb_nav_element sb_alignRight" rel="<?= $popBoxToOpen ?>"><span class="sb_theme_buttonArrow">Take The Survey</span></a>
					</li>
				<? } else { // User cannot take this survey ?>
					<li><h3 class="sb_theme_iconComplete"><?= $survey->title ?></h3>
						<div class="sb_pointsEarnedTotal">
							<span class="sb_chopsEarned">+<?= $numberOfChops ?></span>
							<span class="sb_notesEarned">+<?= $numberOfNotes ?></span>
						</div><!-- .sb_pointsEarnedTotal -->
					</li>
				<? } ?>
				<? 
				$i++;
			}
		}
	}
}
