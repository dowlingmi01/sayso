<?php

require_once APPLICATION_PATH . '/modules/starbar/controllers/ContentController.php';

class Starbar_HellomusicController extends Starbar_ContentController
{
    public function postDispatch() {
		if (!$this->_usingJsonPRenderer) {
        	$this->view->headLink()->appendStylesheet('/css/starbar-hellomusic.css');
    		$this->view->inlineScript()->appendFile('/js/starbar/starbar-new.js');
        }

        parent::postDispatch();
	}

	// Daily deals is probably unique to each starbar
    public function dailyDealsAction ()
    {
		$feedUrl = "http://staging.hellomusic.com/ec/Interpret.aspx?auth=uyskCsCO5jeS2d1fc5";
		$xml = simpleXML_load_file($feedUrl, "SimpleXMLElement", LIBXML_NOCDATA);

		if($xml ===  FALSE) {

		} else {
			$this->view->assign('deals', $xml);
		}
    }
}
