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

		$cache = Api_Registry::get('cache');
		$key = 'dailydeals';
		if ($cache->test($key)) {
			$feed = $cache->load($key);
		} else {
			$handle = fopen($feedUrl, 'r');
			$feed = stream_get_contents($handle);
			$cache->save($feed);
		}

		$xml = simpleXML_load_string($feed, "SimpleXMLElement", LIBXML_NOCDATA);
		
		if($xml ===  FALSE) {

		} else {
			$this->view->assign('deals', $xml);
		}
    }
}
