<?php

/**
 * Game class for HelloMusic
 *
 * This should only contain game logic specific to HelloMusic.
 * Game logic for starbars in general, should be in the parent
 * class Game_Starbar.
 *
 * @author davidbjames
 *
 */
class Game_Starbar_Snakkle extends Game_Starbar {

	/**
	 *
	 * @see Game_Abstract::_visitGood()
	 */
	protected function _visitGood (Gaming_BigDoor_Good $good, $quantity = 1)
	{
		$profile = $this->getGamer();

		// Avoid doing profile survey lookup and check for every good -- use static variables!
		if (parent::$userHasCompletedProfileSurvey === null || parent::$profileSurveyId === null) {
			parent::setStaticProfileSurveyVariables($this->_request);
		}

		$currentLevel = $profile->getHighestLevel();

		if ($good->getId() == $this->_economy->getGoodId('MONTH_ONE_GIVEAWAY')) {
			$good->setNonRedeemReason('Congrats to Spandana B from Chicago, IL on winning the first grand prize!');
			$good->setCommentForUser('Unavailable');
		} elseif ($good->getId() == $this->_economy->getGoodId('MONTH_TWO_GIVEAWAY')) {
			$good->setNonRedeemReason("Congrats to Jennifer G from Levittown, NY for winning our 2nd month's Ipad drawing.");
			$good->setCommentForUser('Unavailable');
		/*} elseif ($good->getId() == $this->_economy->getGoodId('WEEK_THREE_GIVEAWAY')) {
			$good->setNonRedeemReason('Congrats to Matthew L. from Black Mt, NC for winning the week 3 prize!');
			$good->setCommentForUser('Unavailable');
		} elseif ($good->getId() == $this->_economy->getGoodId('WEEK_FOUR_GIVEAWAY')) {
			$good->setNonRedeemReason('HUGE Congrats to Michael A. from Fishers, IN for winning the grand prize!');
			$good->setCommentForUser('Unavailable');
		*/
		} elseif (!$good->isToken() && $good->inventory_sold >= $good->inventory_total) {
			$good->setNonRedeemReason('Sorry, this item is sold out.');
			$good->setCommentForUser('Sold Out');
		} elseif (!parent::$userHasCompletedProfileSurvey) {
			if (parent::$profileSurveyId) $profileSurveyLink = '<a href="//'.BASE_DOMAIN.'/starbar/snakkle/embed-survey?survey_id='.parent::$profileSurveyId.'" class="sb_nav_element" rel="sb_popBox_surveys_hg" title="Take profile survey now!" style="position: relative; top: -5px;">Profile Survey</a>';
			else $profileSurveyLink = "Profile Survey";
			$good->setNonRedeemReason('Must complete<br />'.$profileSurveyLink);
			$good->setCommentForUser('Survey Requirement');
		} elseif ($profile->getCurrencyByTitle('Snakkle Bucks')->current_balance < ($good->cost * $quantity)) {
			$good->setNonRedeemReason('Earn more Snakkle Bucks by<br />completing polls and surveys!');
			$good->setCommentForUser('Insufficient Snakkle Bucks');
		}

		if ($good->inventory_total > $good->inventory_sold && (($good->inventory_total - $good->inventory_sold) < 4)) {
			$good->setCommentForUser('Only '.($good->inventory_total - $good->inventory_sold).' left!');
		}

		if (!$good->isToken() && $profile->getGoods()->hasItem($good->getId())) {
			$good->setNonRedeemReason('You have already<br />purchased this item.<br /><br />You can always buy<br />more tokens for the giveaways!<br />');
			$good->setCommentForUser('Purchased');
		}

		parent::_visitGood($good);
	}

	public function getPurchaseCurrencyId() {
		static $currencyId = 0;
		if (!$currencyId) {
			$currencyId = $this->_economy->getCurrencyId('SNAKKLE_BUCKS');
		}
		return $currencyId;
	}
}
