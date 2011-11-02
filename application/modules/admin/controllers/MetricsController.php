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

    /**
     * Check the database for new metrics data and create a JSON response
     * Called with AJAX
     */
    public function pollAction()
    {
        // format input parameters

        $rows           = array();
        $lastRowId      = $this->_getParam('lastRowId');
        $firstRun       = false;
        $error          = '';
        if(!is_array($lastRowId))
        {
            $firstRun   = true;
            $lastRowId  = array('lastSearchId' => 0, 'lastPageViewId' => 0, 'lastSocialActivityId' => 0);
        }

        // get data

        $builder    = new Metrics_FeedCollection();        
        $rows       = array();
        
        try{
            if(!$firstRun)
            {
                $builder->setLastIds($lastRowId);
            }
            $collection = $builder->run();
            foreach($collection as $entry)
            {
                $this->formatPollResult($rows, $entry, $lastRowId);
            }
        }
        catch(Exception $e)
        {
            $error = $e->getMessage();
        }

        // send out

        $content = array('lastRowId' => $lastRowId, 'lastUpdated' => date('h:i:s a'), 'rows' => $rows, 'lastError' => $error);
        echo json_encode($content);
        exit(0);
    }

    /**
     * Format a row for JSON before diplaying it
     *
     * @param array $rows
     * @param array $entry
     * @param array $lastRowId
     */
    private function formatPollResult(&$rows, &$entry, &$lastRowId)
    {
        $index = 'lastSearchId';
        switch($entry['metricsType'])
        {
            case 'Page View':
                $index = 'lastPageViewId';
                break;
            case 'Social Activity':
                $index = 'lastSocialActivityId';
                break;
            default:
                break;
        }

        $lastRowId[$index] = $entry['lastId'] > $lastRowId[$index] ? $entry['lastId'] : $lastRowId[$index];
        
        $rows[] = array
        (
            'userId'        => $entry['userId'],
            'userName'      => (is_null($entry['userName']) ? 'NAME UNSPECIFIED' : $entry['userName']),
            'metricsType'   => $entry['metricsType'],
            'starbar'       => $entry['starbar'],
            'dateTime'      => $entry['dateTime'],
            'data'          => $entry['data'],
        );
    }
}