<?php
class Starbar_View_Helper_DisplaySurveys extends Zend_View_Helper_Abstract
{
	function displaySurveys($status, $starbar) {

		$numberOfPremiumRedeemablePoints = 375;
		$numberOfPremiumExperiencePoints = 5000;
		$numberOfStandardRedeemablePoints = 38;
		$numberOfStandardExperiencePoints = 500;
		$numberOfProfileRedeemablePoints = 150;
		$numberOfProfileExperiencePoints = 2000;

		$numberOfPremiumRedeemablePointsDisqualified = 75;
		$numberOfPremiumExperiencePointsDisqualified = 1000;
		$numberOfStandardRedeemablePointsDisqualified = 25;
		$numberOfStandardExperiencePointsDisqualified = 250;
		$numberOfProfileRedeemablePointsDisqualified = 75;
		$numberOfProfileExperiencePointsDisqualified = 1000;

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

		if ($numberToShow) {
			$i = 0;

			if ($status == 'new' || $status == 'archived') {
				echo '<ul class="sb_solidList">';
			}

			foreach ($surveys as $survey) {
				// The numberToShow can be smaller than the size of the list
				if ($i >= $numberToShow) break;

				// "sb_listOdd" or "sb_listEven"
				$listItemClass = "sb_list".(($i % 2) ? "Odd" : "Even");

				// Used for new and archived, ignored for completed/disqualified
				if ($survey->origin == "UGAM") {
					$popBoxToOpen = "sb_popBox_surveys_ug";
				} else if ($survey->size == "large") {
					$popBoxToOpen = "sb_popBox_surveys_hg";
				} else {
					$popBoxToOpen = "sb_popBox_surveys_lg";
				}

				// Used for new and archived, ignored for completed/disqualified
				switch($survey->reward_category) {
					case "standard":
						$strongClass = "sb_theme_textHighlight";
						$buttonClass = "sb_theme_button";
						break;
					case "premium":
					case "profile":
						$strongClass = "sb_theme_textHighlight_alt";
						$buttonClass = "sb_theme_button sb_theme_button_alt";
						break;
				}

				if (in_array($status, array("new", "archived", "completed"))) {
					switch($survey->reward_category) {
						case "standard":
							$numberOfRedeemablePoints = $numberOfStandardRedeemablePoints;
							$numberOfExperiencePoints = $numberOfStandardExperiencePoints;
							break;
						case "premium":
							$numberOfRedeemablePoints = $numberOfPremiumRedeemablePoints;
							$numberOfExperiencePoints = $numberOfPremiumExperiencePoints;
							break;
						case "profile":
							$numberOfRedeemablePoints = $numberOfProfileRedeemablePoints;
							$numberOfExperiencePoints = $numberOfProfileExperiencePoints;
							break;
					}
				} elseif ($status == 'disqualified') {
					switch($survey->reward_category) {
						case "standard":
							$numberOfRedeemablePoints = $numberOfStandardRedeemablePointsDisqualified;
							$numberOfExperiencePoints = $numberOfStandardExperiencePointsDisqualified;
							break;
						case "premium":
							$numberOfRedeemablePoints = $numberOfPremiumRedeemablePointsDisqualified;
							$numberOfExperiencePoints = $numberOfPremiumExperiencePointsDisqualified;
							break;
						case "profile":
							$numberOfRedeemablePoints = $numberOfProfileRedeemablePointsDisqualified;
							$numberOfExperiencePoints = $numberOfProfileExperiencePointsDisqualified;
							break;
					}
				}

				?>
				<? if ($status == 'new' || $status == 'archived') { // User can take this survey ?>
					<li class="<?= $listItemClass ?>">
						<div class="sb_surveyInfo">
						<? if ($survey->reward_category == "premium") { ?>
								<span class="sb_img_x10Value"></span>
							<? } ?>
							<h3><?= $survey->title ?></h3>
							<p><? if ($survey->display_number_of_questions) { ?><?= $survey->display_number_of_questions ?> Questions - <? } ?>Earn <strong class="<?= $strongClass ?>"><?= $numberOfRedeemablePoints ?> <span class="sb_currency_title" data-currency-type="redeemable"></span></strong> and <strong class="<?= $strongClass ?>"><?= $numberOfExperiencePoints ?> <span class="sb_currency_title" data-currency-type="experience"></span></strong></p>
						</div><!-- .sb_surveyInfo -->
						<a href="//<?= BASE_DOMAIN ?>/starbar/<?= ($starbar->id == 3 ? "content" : $starbar->short_name) ?>/embed-survey?survey_id=<?= $survey->id ?>" class="sb_surveyLaunch sb_nav_element sb_alignRight" rel="<?= $popBoxToOpen ?>"><span class="sb_survey-select">Take The Survey</span></a>
					</li>
				<? } else { // User cannot take this survey ?>
					<li><h3 class="sb_theme_iconComplete"><?= $survey->title ?></h3>
						<div class="sb_pointsEarnedTotal">
							<span class="sb_xpEarned">+<?= $numberOfExperiencePoints ?></span>
							<span class="sb_notesEarned">+<?= $numberOfRedeemablePoints ?></span>
						</div><!-- .sb_pointsEarnedTotal -->
					</li>
				<? } ?>
				<?
				$i++;
			}
			if ($status == 'new' || $status == 'archived') {
				echo '</ul>';
			}
		}
	}
}
