<?php
class Starbar_View_Helper_DisplayPolls extends Zend_View_Helper_Abstract
{
	function displayPolls($type) {

		switch ($type) {
			case "new":
				$polls = $this->view->new_polls;
				$numberToShow= $this->view->count_new_polls;
				break;

			case "completed":
				$polls = $this->view->completed_polls;
				$numberToShow= $this->view->count_completed_polls;
				break;

			case "disqualified":
				$polls = $this->view->disqualified_polls;
				$numberToShow= $this->view->count_disqualified_polls;
				break;

			case "archived":
				$polls = $this->view->archived_polls;
				$numberToShow= $this->view->count_archived_polls;
				break;

			default:
				return;
		}

		if ($polls) {
			$i = 0;
			foreach ($polls as $survey) {
				// The numberToShow can be smaller than the size of the list
				if ($i >= $numberToShow) break;
				?>
				<? if ($type == 'new' || $type == 'archived') { // User can take this poll ?>
					<h3>
						<? $iframeHeight = 62 + (ceil($survey->number_of_answers / 2.0) * 32); // 62 base height + 32 per row of answers -- Note that this height estimate is updated after the iframe loads ?>
						<a href="http://<?= BASE_DOMAIN ?>/starbar/hellomusic/embed-poll?survey_id=<?= $survey->id ?>" rel="starbar-poll-<?= $survey->id ?>" iframeHeight="<?= $iframeHeight ?>">
							<? if ($survey->premium) { ?>
								<span class="sb_img_doubleValue"></span>
							<? } ?>
							<?= $survey->title ?>
						</a>
					</h3>
					<div>
						<? if ($i < ($numberToShow - 1)) { ?>
							<div class="sb_nextPoll">
								<a href="#" onclick="$SQ(this).parents('.sb_accordion').accordion('activate', <?= ($i+1) ?>)"><span class="sb_img_nextTriangle"></span>Next Poll</a>
							</div>
						<? } ?>
						<div id="starbar-poll-<?= $survey->id ?>">
							<div class="sayso-starbar-loading-external">
								<span class="sb_img_loading">Loading</span>
							</div>
						</div>
					</div><!-- / END ACCORDION CONTENT -->
				<? } else { // User cannot take this poll ?>
					<li><h3 class="sb_theme_iconComplete"><?= $survey->title ?></h3>
              			<div class="sb_pointsEarnedTotal">
              				<? if ($survey->premium) { ?>
                				<span class="sb_xpEarned sb_theme_textHighlight_alt">+500</span>
                				<span class="sb_notesEarned sb_theme_textHighlight_alt">+50</span>
							<? } else { ?>
                				<span class="sb_xpEarned sb_theme_textHighlight">+250</span>
                				<span class="sb_notesEarned sb_theme_textHighlight">+25</span>
							<? } ?>
						</div><!-- .sb_pointsEarnedTotal -->
					</li>
				<? } ?>
				<? 
				$i++;
			}
		}
	}
}
	