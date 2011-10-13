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
    public function userProfileAction () {
        $this->_validateRequiredParameters(array('user_id', 'starbar_id'));
        $gamer = Gamer::create($this->user_id, $this->starbar_id);
        $game = Game_Starbar::create($gamer, $this->_request);
        $gamer->loadProfile($game->getHttpClient());
        return $this->_resultType($gamer);
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


