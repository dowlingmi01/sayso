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
        $profile = $this->getGamer();
        if ($good->getId() !== $this->_economy->getGoodId('WEEK_ONE_GIVEAWAY')) {
            $currencyPrimarySurveyId = $this->_economy->getCurrencyId('PRIMARY_SURVEY_POINTS');
            $currencyPrimarySurvey = $profile->getCurrencies()->find('id', $currencyPrimarySurveyId)->getFirst();
            if ((int) $currencyPrimarySurvey->current_balance < 2) {
                $good->setIsRedeemable(false);
            }
        } 
        // for goods other than first weekly give away
        // if user has finished primary survey
        // set redeemable true
        parent::_visitGood($good);
        
    }

     
}