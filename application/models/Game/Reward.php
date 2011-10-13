<?php

class Game_Reward extends Item
{
    /**
     * Array of all currencies for all Starbars.
     * Since they have globally unique IDs it is safe
     * to store them here together.
     * 
     * @var array
     */
    protected static $_currencies = array(
    	'67383' => 'chops', 
    	'67384' => 'notes'
    );
    
    /**
     * Start time
     * 
     * @var Date
     */
    protected $_start;
    
    /**
     * End time
     * 
     * @var Date
     */
    protected $_end;
    
    /**
     * Build out a Reward object
     * 
     * @param array | Iterator $data 
     * @param Builder $builder 
     */
    public function build (& $data, $builder = null) {
        $costData = $data->cost[0];
        $this->cost = abs((int) $costData->default_amount);
        $this->currency = self::$_currencies[$costData->currency_id];
        $goodsData = $data->goods[0];
        $this->setId($goodsData->named_good_id);
        $this->description = $goodsData->end_user_description;
        $this->inventory_sold = $goodsData->sold_inventory;
        $this->inventory_total = $goodsData->total_inventory;
        $this->_start = new Date($goodsData->available_start);
        $this->_end = new Date($goodsData->available_end);
        $this->title = trim(str_replace('(Full)', '', $goodsData->urls[0]->pub_title));
        $this->url_full = trim($goodsData->urls[0]->url);
        $this->url_preview = trim($goodsData->urls[1]->url);
    }
    
    public function isLocked () {
        return false;
    }
}
