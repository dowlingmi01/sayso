<?php
class Starbar_View_Helper_DisplayPolls extends Zend_View_Helper_Abstract
{
	function displayPolls($status) {

		switch ($status) {
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

		if ($numberToShow) {
			$i = 0;
			if ($status == 'new' || $status == 'archived') {
				echo '<div class="sb_accordion">';
			}
			foreach ($polls as $survey) {
				// The numberToShow can be smaller than the size of the list
				if ($i >= $numberToShow) break;
				?>
				<? if ($status == 'new' || $status == 'archived') { // User can take this poll ?>
					<h3>
						<? $iframeHeight = 62 + (ceil($survey->number_of_answers / 2.0) * 32); // 62 base height + 32 per row of answers -- Note that this height estimate is updated after the iframe loads ?>
						<a href="https://<?= BASE_DOMAIN ?>/starbar/hellomusic/embed-poll?survey_id=<?= $survey->id ?>" rel="starbar-poll-<?= $survey->id ?>" iframeHeight="<?= $iframeHeight ?>">
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
			if ($status == 'new' || $status == 'archived') {
				echo '</div><!-- .accordion -->';
			}

		} elseif ($status == 'new') { // No new polls, show a message!
			?>
			<? if ($this->view->count_archived_polls) { ?>
				<p>No new polls today, but you still have <?= $this->view->count_archived_polls ?> polls to complete in the <a href="#" class="sb_nav_tabs" rel="<?= (($this->view->count_completed_polls || $this->view->count_disqualified_polls) ? 3 : 2) ?>">archives</a>.</p>
			<? } else { ?>
				<p>No new polls today, check back soon to earn more notes and chops!</p>
			<? } ?>
			<?
		}
	}
}
	