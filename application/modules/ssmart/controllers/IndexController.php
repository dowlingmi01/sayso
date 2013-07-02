<?php
/**
 * <p>This controller is the end point for all external requests.</p>
 * <p>It sets up the Api object with a request, response, and auth object.
 * Then sets to json view.
 * Then processes the request.</p>
 *
 * @package Ssmart
 */
class Ssmart_IndexController extends Zend_Controller_Action
{
	/**
	 * Handles the index request for this controller.
	 *
	 * <p>Gets the request data</p>
	 * <p>Instansiates the api</p>
	 * <p>Disables the layout</p>
	 * <p>Sets the output format</p>
	 * <p>Gets the api response.</p>
	 */
	public function indexAction()
	{
		//initiallize variables
		$requestData = $this->getRequest()->getParam('data');

		//only process if a request has been made.
		if ($requestData)
		{
			$api = Ssmart_Api::getInstance(NULL, $requestData);
			$this->_disableLayout();
			$response = $api->getResponse();
			$this->_helper->viewRenderer->setRender('json');
			$this->view->api_response = $response;
		}
	}

	/**
	 * Alias method to disable layout script
	 *
	 */
	protected function _disableLayout ()
	{
		try {
			$this->_helper->layout()->disableLayout();
		} catch (Exception $e) {}
	}
}