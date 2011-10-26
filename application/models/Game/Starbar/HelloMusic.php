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
        
        // determine whether good is redeemable
        if ($good->getId() !== $this->_economy->getGoodId('WEEK_ONE_GIVEAWAY')) {
            $currencyPrimarySurveyId = $this->_economy->getCurrencyId('PRIMARY_SURVEY_POINTS');
            $currencyPrimarySurvey = $profile->getCurrencies()->find('id', $currencyPrimarySurveyId)->getFirst();
            $currentLevel = $profile->getHighestLevel();
            if ((int) $currencyPrimarySurvey->current_balance < 1 || 
                $currentLevel->ordinal < $buskerLevel->ordinal) {
                $good->isRedeemable(false);
            }
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