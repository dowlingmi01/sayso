<?php
/**
 * @author alecksmart
 */

require_once APPLICATION_PATH . '/modules/admin/controllers/CommonController.php';

class Cms_MetricsController extends Admin_CommonController
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

		$rows		   = array();
		$onlyUser	   = isset($_COOKIE['control-metrics-user-only']) ? intval($_COOKIE['control-metrics-user-only']) : 0;
		$error		  = '';

		// get data

		$builder	= new Metrics_LogCollection();

		try
		{
			$criteria = array
			(
				'onlyUser'	  => $onlyUser,
				'rowId'		 => $this->_getParam('rowId'),
				'direction'	 => $this->_getParam('dir'),
			);
			$builder->setCriteria($criteria);
			$builder->setTypes($this->_getParam('pollForTypes'));
			$collection = $builder->run();

			foreach($collection as $entry)
			{
				$this->formatPollResult($rows, $entry);
			}
		}
		catch(Exception $e)
		{
			$error = $e->getMessage();
		}

		// send out

		$content = array('lastUpdated' => date('h:i:s a'), 'rows' => $rows, 'lastError' => $error);
		echo json_encode($content);
		exit(0);
	}

	/**
	 * Format a row for JSON before diplaying it
	 *
	 * @param array $rows
	 * @param array $entry
	 */
	private function formatPollResult(&$rows, &$entry)
	{
		array_unshift($rows, $entry);
	}
}