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
        // format input parameters

        $lastRowId      = 0;
        $rows           = array();
        $lastFromPost   = $this->_getParam('lastRowId');
        if($lastFromPost > 0)
        {
            $lastRowId = $lastFromPost;
        }

        $rows = array(
            array(
                'userId'        => '1',
                'userName'      => 'Some User Name',
                'metricsType'   => 'Page View',
                'starbar'       => 'Starbar',
                'dateTime'      => date('Y-m-d H:i:s'),
                'data'          => 'Some data to show',
            ),
        );

        // get data

        $this->pollPageViews($rows, $lastRowId);
        $this->pollSearches($rows, $lastRowId);
        $this->pollSocialActivity($rows, $lastRowId);

        // send out

        $content = array('lastRowId' => $lastRowId, 'lastUpdated' => date('h:i:s a'), 'rows' => $rows);
        echo json_encode($content);
        exit(0);
    }

    /**
     * Data formatting functions for JSON poller
     */

    private function pollPageViews(&$result, &$lastRowId)
    {

    }

    private function pollSearches(&$result, &$lastRowId)
    {

    }

    private function pollSocialActivity(&$result, &$lastRowId)
    {

    }

    private function formatPollResult(&$result, $data)
    {
        if(!empty($data))
        {
            $result[] = array(
                'userId'        => $data['userId'],
                'userName'      => $data['userName'],
                'metricsType'   => $data['metricsType'],
                'starbar'       => $data['starbar'],
                'dateTime'      => $data['dateTime'],
                'data'          => $data['data'],
            );
        }
    }
}