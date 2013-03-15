<?php
/**
 * This controller is the end point for all external requests.
 * It sets up the Api object with a request, response, and auth object
 * then sets to json view
 * then processes the request
 */
class Api3_IndexController extends Zend_Controller_Action
{
	public function indexAction()
	{
		//initiallize variables
		$requestData = $this->getRequest()->getParam('data');

		//only process if a request has been made.
		if ($requestData)
		{
			$api = Api3_Api::getInstance(NULL, NULL, NULL, $requestData);
			$this->_disableLayout();
			$this->_helper->viewRenderer->setRender('json');
			$this->view->api_response = $api->getResponse();
		}
	}

	/**
	 * Alias method to disable layout script
	 *
	 * Stole this from the Global_Api class
	 */
	protected function _disableLayout ()
	{
		try {
			$this->_helper->layout()->disableLayout();
		} catch (Exception $e) {}
	}
}