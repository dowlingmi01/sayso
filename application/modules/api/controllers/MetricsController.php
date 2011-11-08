<?php

require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Api_MetricsController extends Api_GlobalController
{
    
    public function init()
    {
        
    }

    public function indexAction()
    {
    }
    
    public function testAction () {
        return $this->_resultType(new Object(array('foo' => 'bar')));
    }
    
    public function pageViewSubmitAction () {
        $this->_validateRequiredParameters(array('user_id', 'user_key', 'starbar_id', 'url'));
//        $this->_authenticateUser(true);
        $metric = new Metrics_PageView();
        $metric->user_id = $this->user_id;
        $metric->starbar_id = $this->starbar_id;
        $metric->url = $this->url;
        $metric->save();
        
        return $this->_resultType($metric);
        
    }
    
    public function searchEngineSubmitAction () {
        $this->_validateRequiredParameters(array('user_id', 'user_key', 'starbar_id', 'query', 'type_id'));
        
        $metric = new Metrics_Search();
        $metric->user_id = $this->user_id;
        $metric->starbar_id = $this->starbar_id;
        $metric->search_engine_id = $this->type_id;
        $metric->query = $this->query;
        $metric->save();
        
        return $this->_resultType($metric);
    }

    public function socialActivitySubmitAction ()
    {
        $this->_validateRequiredParameters(array('user_id', 'user_key', 'starbar_id', 'type_id'));
        
        $metric                             = new Metrics_SocialActivity();
        $metric->user_id                    = $this->user_id;
        $metric->starbar_id                 = $this->starbar_id;
        $metric->social_activity_type_id    = $this->type_id;
        
        switch(intval($this->type_id))
        {
            case 1:
                // facebook "Like"
                $metric->url        = $this->url;
                break;
            default:
                // currently it is twitter only
                $metric->url        = $this->url;
                $metric->content    = $this->content;
                break;
        }

        $metric->save();
        
        return $this->_resultType($metric);
    }
}


