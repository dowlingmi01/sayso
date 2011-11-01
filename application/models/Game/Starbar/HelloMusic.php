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

		if ($good->inventory_total != -1 && $good->inventory_sold >= $good->inventory_total) {
			$good->setNonRedeemReason('Sorry, this item was sold out.');
			$good->setCommentForUser('Sold Out');
		} elseif ((int) $currencyPrimarySurvey->current_balance < 1 && $good->getId() !== $this->_economy->getGoodId('WEEK_ONE_GIVEAWAY')) {
			$good->setNonRedeemReason('Must complete<br /><a href="http://'.BASE_DOMAIN.'/starbar/hellomusic/embed-survey?survey_id=1" class="sb_nav_element" rel="sb_popBox_surveys_hg" title="Take influencer survey now!" style="position: relative; top: -5px;">Influencer Survey</a>');
			$good->setCommentForUser('Survey Requirement');
		} elseif ($currentLevel->ordinal < $buskerLevel->ordinal) {
			$good->setNonRedeemReason('Must reach Level 2:<br /><strong>Busker Level</strong>');
			$good->setCommentForUser('Level Requirement');
		} elseif ($profile->getCurrencyByTitle('notes')->current_balance < $good->cost) {
			$good->setNonRedeemReason('Earn more notes by completing polls and surveys!');
			$good->setCommentForUser('Insufficient Notes');
		}/* elseif () {
			$good->setNonRedeemReason('Nice! You have purchased this item.');
			$good->setCommentForUser('Purchased');
		}*/

		if ($good->inventory_total > $good->inventory_sold && (($good->inventory_total - $good->inventory_sold) < 4)) {
			$good->setCommentForUser('Only '.($good->inventory_total - $good->inventory_sold).' left!');
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