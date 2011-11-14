<?php
/**
 * @author alecksmart
 */

require_once APPLICATION_PATH . '/modules/admin/controllers/CommonController.php';

class Admin_MetricsController extends Admin_CommonController
{

    public function init()
    {
        if (!$this->_request->isXmlHttpRequest())
        {
            $this->setLayoutBasics();
        }
        parent::init();
    }

    public function indexAction()
    {

        if(!$this->checkAccess(array('superuser')))
        {
            $this->_helper->viewRenderer->setNoRender(true);
        }

        $this->view->headLink()->appendStylesheet('/modules/admin/metrics/index.css', 'screen');
        $this->view->headScript()->appendFile('/js/jquery.cookie.min.js');
        $this->view->headScript()->appendFile('/js/jquery.ba-dotimeout.min.js');
        $this->view->headScript()->appendFile('/modules/admin/metrics/index.js');

        /*
        $options = array();
        $tableTest = new Data_Markup_Grid($options);
        var_dump(get_class($tableTest));
         */
    }

    /**
     * Check the database for new metrics data and create a JSON response
     * Called with AJAX
     */
    public function pollAction()
    {
        if(!$this->checkAccess(array('superuser')))
        {
            die('Access denied!');
        }

        // format input parameters

        $rows           = array();
        $lastRowId      = $this->_getParam('lastRowId');
        $pollForTypes   = $this->_getParam('pollForTypes');
        $onlyUser       = isset($_COOKIE['control-metrics-user-only'])
                            ? intval($_COOKIE['control-metrics-user-only'])
                            : 0;

        //var_dump($onlyUser);exit(0);

        $firstRun       = false;
        $error          = '';
        if(!is_array($lastRowId))
        {
            $firstRun   = true;
            $lastRowId  = array
            (
                'lastSearchId'          => 0,
                'lastPageViewId'        => 0,
                'lastSocialActivityId'  => 0,
                'lastTagId'             => 0,
                'lastTagViewId'         => 0,
                'lastCreativeId'        => 0,
                'lastCreativeViewId'    => 0,
                'rowsAfter'             => '0000-00-00 00:00:00'
            );
        }

        // get data

        $builder    = new Metrics_FeedCollection();
        $rows       = array();

        try{

            $criteria = array('onlyUser' => $onlyUser);
            $builder->setCriteria($criteria);

            if(!$firstRun)
            {
                $builder->setLastIds($lastRowId);
            }
            $builder->setTypes($pollForTypes);
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
            case 'Tag':
                if($entry['selectType'] == 1)
                {
                    $index = 'lastTagId';
                }
                else
                {
                    $index = 'lastTagViewId';
                }
                break;
            case 'Creative':
                $index = 'lastCreative';
                break;
            default:
                break;
        }

        $lastRowId[$index]      = $entry['lastId'] > $lastRowId[$index] ? $entry['lastId'] : $lastRowId[$index];
        $lastRowId['rowsAfter'] = $entry['dateTime'] > $lastRowId['rowsAfter'] ? $entry['dateTime'] : $lastRowId['rowsAfter'];

        // use unshift to send feed in the reverse order
        // for feed formatter
        array_unshift($rows, array(
            'userId'        => $entry['userId'],
            //'userName'      => (is_null($entry['userName']) ? 'NAME UNSPECIFIED' : $entry['userName']),
            'metricsType'   => $entry['metricsType'],
            'starbar'       => $entry['starbar'],
            'dateTime'      => $entry['dateTime'],
            'data'          => $entry['data'],
        ));
    }
}