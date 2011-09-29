<?php

require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Api_StarbarController extends Api_GlobalController
{
    
    public function init()
    {
        
    }

    public function indexAction()
    {
    }
    
    public function setOnboardStatusAction () {
        $this->_validateRequiredParameters(array('starbar_id', 'user_id', 'status'));
        $starbarUserMap = new Starbar_UserMap();
        $starbarUserMap->loadDataByUniqueFields(array('starbar_id' => (int) $this->starbar_id, 'user_id' => (int) $this->user_id));
        $starbarUserMap->onboarded = (int) $this->status;
        $starbarUserMap->save();
        return $this->_resultType($starbarUserMap);
    }
}


