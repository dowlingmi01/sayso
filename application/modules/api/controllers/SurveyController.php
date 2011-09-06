<?php

require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Api_SurveyController extends Api_GlobalController
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
    }
    
    public function surveyGizmoSubmitAction () {
        
        // @todo identify WHICH survey is being submitted. this must match an existing survey in the survey table
        // @todo identify user via 'id' param which must match existing user in user table. Then record that this user has completed the survey in survey_user_map table
        
        // dumping the post data into the log (remove this after testing)
        $logger = new Zend_Log();
        $logger->addWriter(new Zend_Log_Writer_Stream(LOG_PATH . '/api.log'));
        $logger->log(print_r($this->_request->getPost(), true), Zend_Log::INFO);
        
        // success
        return $this->_resultType(true);
    }

}

