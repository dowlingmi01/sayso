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
class Game_Starbar_HelloMusic extends Game_Starbar {

	/**
	 *
     * @see Game_Abstract::_visitGood()
     */
    protected function _visitGood (Gaming_BigDoor_Good $good)
    {
        static $buskerLevel = null;
        if (!$buskerLevel) {
            $buskerLevel = $this->getLevels()->find('title', 'busker')->getFirst();
        }
        $profile = $this->getGamer();

		$currencyPrimarySurveyId = $this->_economy->getCurrencyId('PRIMARY_SURVEY_POINTS');
		$currencyPrimarySurvey = $profile->getCurrencies()->find('id', $currencyPrimarySurveyId)->getFirst();
		$currentLevel = $profile->getHighestLevel();

		if ($good->getId() == $this->_economy->getGoodId('WEEK_ONE_GIVEAWAY')) {
			$good->setNonRedeemReason('It\'s too late to buy tokens for week 1: we\'ll be announcing the winner soon!');
			$good->setCommentForUser('Unavailable');
		} elseif (!$good->isToken() && $good->inventory_sold >= $good->inventory_total) {
			$good->setNonRedeemReason('SOLD OUT? There\'s more<br />gear coming! Check back and keep earning!');
			$good->setCommentForUser('Sold Out');
		} elseif ((int) $currencyPrimarySurvey->current_balance < 1 && $good->getId() !== $this->_economy->getGoodId('WEEK_ONE_GIVEAWAY')) {
			$good->setNonRedeemReason('Must complete<br /><a href="//'.BASE_DOMAIN.'/starbar/hellomusic/embed-survey?survey_id=1" class="sb_nav_element" rel="sb_popBox_surveys_hg" title="Take influencer survey now!" style="position: relative; top: -5px;">Influencer Survey</a>');
			$good->setCommentForUser('Survey Requirement');
		} elseif ($currentLevel->ordinal < $buskerLevel->ordinal && $good->getId() !== $this->_economy->getGoodId('WEEK_ONE_GIVEAWAY')) {
			$good->setNonRedeemReason('Must reach Level 2:<br /><strong>Busker Level</strong>');
			$good->setCommentForUser('Level Requirement');
		} elseif ($profile->getCurrencyByTitle('notes')->current_balance < $good->cost) {
			$good->setNonRedeemReason('Earn more notes by<br />completing polls and surveys!');
			$good->setCommentForUser('Insufficient Notes');
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
            $currencyId = $this->_economy->getCurrencyId('NOTES');
        }
        return $currencyId;
    }
}