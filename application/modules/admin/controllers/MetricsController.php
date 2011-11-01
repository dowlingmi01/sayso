<?php
/**
 * @author alecksmart
 */
class Admin_MetricsController extends Api_AbstractController
{

    public function init()
    {
        if (!$this->_request->isXmlHttpRequest()) 
        {
            $this->_helper->layout->setLayout('admin');
            $this->view->headLink()->appendStylesheet('/css/common.css', 'screen');
            $this->view->headLink()->appendStylesheet('/css/smoothness/jquery-ui-1.8.13.custom.css', 'screen');
            $this->view->headScript()->appendFile('/js/jquery-1.6.1.min.js');
            $this->view->headScript()->appendFile('/js/jquery.form.min.js');
            $this->view->headScript()->appendFile('/js/jquery-ui-1.8.13.custom.min.js');                        
        }
        parent::init();
    }

    public function indexAction()
    {
        $this->view->headScript()->appendFile('/js/jquery.ba-dotimeout.min.js');
        $this->view->headScript()->appendFile('/modules/admin/metrics/index.js');
        $this->view->headLink()->appendStylesheet('/modules/admin/metrics/index.css', 'screen');
    }

    public function pollAction()
    {        
        $lastRowId      = 0;
        $lastFromPost   = intval($this->_getParam('lastRowId'));
        if($lastFromPost > 0)
        {
            $lastRowId = $lastFromPost;
        }
        $content = array('lastRowId' => $lastRowId, 'lastUpdated' => date('h:i:s a'));
        echo json_encode($content);
        exit(0);
    }
}