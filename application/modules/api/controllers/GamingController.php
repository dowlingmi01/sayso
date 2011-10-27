<?php

require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Api_GamingController extends Api_GlobalController
{
    
    public function init()
    {
        
    }

    public function indexAction()
    {
        // possibly use this controller as the mini-gaming API for stuff
        // that is purely client side, such as Twitter sharing
    }
    
    // http://local.sayso.com/api/gaming/user-profile/starbar_id/1/user_id/46/user_key/r3nouttk6om52u18ba154mc4j4/auth_key/309e34632c2ca9cd5edaf2388f5fa3db
    
    public function getGameAction () {
        $game = Game_Starbar::getInstance();
        $game->loadGamerProfile(); // get latest points after transaction
        return $this->_resultType($game);
    }
    
    /**
     * Get RAW user profile from Big Door
     * - use this for testing only. see next method for standard use
     * 
     */
    public function userProfileRawAction () {
        $game = Game_Starbar::getInstance();
        $gamer = $game->getGamer(false);
        $client = $game->getHttpClient();
        $client->getEndUser($gamer->getGamingId());
        return $this->_resultType($client->getData(true));
    }
    
    /**
     * Get User profile via our own objects
     * Enter description here ...
     */
    public function userProfileAction () {
        $this->_validateRequiredParameters(array('user_id', 'starbar_id'));
        $gamer = Gamer::create($this->user_id, $this->starbar_id);
        $game = Game_Starbar::create($gamer, $this->_request);
        $gamer->loadProfile($game->getHttpClient());
        return $this->_resultType($gamer);
    }
    
    /**
     * Get levels THIS IS STILL IN PROGRESS
     * 
     */
    public function levelsAction () {
        
        $client = new Gaming_BigDoor_HttpClient('2107954aa40c46f090b9a562768b1e18', '76adcb0c853f486297933c34816f1cd2');
        $client->getNamedLevelCollection(43352);
        $data = $client->getData();
        $levels = new ItemCollection();
        foreach ($data->named_levels as $levelData) {
            $level = new Gaming_BigDoor_Level();
            $level->setId($levelData->id);
            $level->title = $levelData->end_user_title;
            $level->description = $levelData->end_user_description;
            $level->urls = Gaming_BigDoor_Url::buildUrlCollection($levelData->urls);
            $level->timestamp = $levelData->created_timestamp;
            $level->ordinal = $levelData->threshold;
            $levels[] = $level;
        }
        return $this->_resultType($levels);
//        return $this->_resultType($client->getData(true)); // raw data
    }
    
    public function getGoodAction () {
        $this->_validateRequiredParameters(array('good_id'));
        $game = Game_Starbar::getInstance();
        $client = $game->getHttpClient();
        
        $client->getNamedGood($this->good_id);
        $data = $client->getData();
        $good = new Gaming_BigDoor_Good();
        $good->build($data);
        $good->accept($game);
        return $this->_resultType($good);
    }
    
    /**
     * 
     * @return Gaming_BigDoor_Good
     */
    public function getGoodFromStoreAction () {
        $this->_validateRequiredParameters(array('good_id'));
        $goods = $this->getGoodsFromStoreAction();
        $good = $goods->getItem($this->good_id); 
        if (isNull($good)) {
            throw new Api_Exception(Api_Error::create(Api_Error::GAMING_ERROR, 'Good ID ' . $this->good_id . ' not found in store'));
        }
        return $this->_resultType($good);
    } 
    
    /**
     * 
     * @return ItemCollection
     */
    public function getGoodsFromStoreAction () {
        $game = Game_Starbar::getInstance();
        $cache = Api_Cache::getInstance('BigDoor_getNamedTransactionGroup_store', Api_Cache::LIFETIME_WEEK);
	    if ($cache->test()) {
	        $data = $cache->load();
	    } else {
	        $client = $game->getHttpClient();
            $client->setCustomParameters(array(
            	'attribute_friendly_id' => 'bdm-product-variant', 
            	'verbosity' => 9,
                'max_records' => 100
            ));
            $client->getNamedTransactionGroup('store');
            $data = $client->getData();
            $cache->save($data);
	    }
	    
        $goods = new ItemCollection(); 
        foreach ($data as $goodData) {
            $good = new Gaming_BigDoor_Good();
            $good->setPrimaryCurrencyId($game->getPurchaseCurrencyId());
            $good->build($goodData);
            $good->accept($game);
            $goods[] = $good;
        }
        
        return $this->_resultType($goods);
    }
    
    public function shareAction () {
        $this->_validateRequiredParameters(array('shared_type', 'shared_id'));
        
    	Game_Starbar::getInstance()->share($this->shared_type, @$this->shared_id);
        return $this->_resultType(true);
	}
    
    public function checkinAction () {
    	Game_Starbar::getInstance()->checkin();
        return $this->_resultType(true);
	}
    
    public function testBigDoorAction () {
        $this->_validateRequiredParameters(array('user_id', 'starbar_id'));
        
        $gamer = Gamer::create($this->user_id, $this->starbar_id);
        Game_Starbar::create($gamer, $this->_request)->trigger();
        
        $user = new User();
        $user->loadData($this->user_id);
        
        return $this->_resultType($user);
    }
}


