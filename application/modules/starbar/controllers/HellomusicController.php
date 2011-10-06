<?php

require_once APPLICATION_PATH . '/modules/starbar/controllers/ContentController.php';

class Starbar_HellomusicController extends Starbar_ContentController
{
    public function postDispatch() {
		if (!$this->_usingJsonPRenderer) {
        	$this->view->headLink()->appendStylesheet('/css/starbar-hellomusic.css');
        }

        parent::postDispatch();
	}

	// Daily deals is probably unique to each starbar
    public function dailyDealsAction ()
    {
		$feedUrl = "http://www.hellomusic.com/ec/Interpret.aspx?auth=uyskCsCO5jeS2d1fc5";

		$feed = null;
		$cache = Api_Registry::get('cache');
		$key = 'dailydeals';

		if ($cache->test($key)) {
			$feed = $cache->load($key);
		}

		// if the feed is no longer cached, or if it's empty for whatever reason, re-set the cache
		if (!$feed) {
			$handle = fopen($feedUrl, 'r');
			$feed = stream_get_contents($handle);

			// cache until the next update
			$cache->setLifetime(3600);

			$cache->save($feed);
		}

		$xml = simpleXML_load_string($feed, "SimpleXMLElement", LIBXML_NOCDATA);
		
		if($xml ===  FALSE) {

		} else {
			$this->view->assign('deals', $xml);
			
			$facebookCallbackUrl = "http://".BASE_DOMAIN."/starbar/hellomusic/facebook-post-result?shared=deal&shared_id=THE_DEAL_ID&user_id=".$this->user_id."&user_key=".$this->user_key."&auth_key=".$this->auth_key;
			$this->_assignShareInfoToView(null, null, $facebookCallbackUrl);
		}
    }
    
    public function facebookPostResultAction ()
    {
    	// this page is fetched in an iframe, not ajax
    	$this->_usingJsonPRenderer = false;

		$request = $this->getRequest();

        $success = false;

		/* Facebook wall post successful */
		if ($request->getParam('post_id')) {
			$success = true;
			// @todo Reward user here based on $request->getParam('shared') (e.g. poll, survey, daily-deal, etc.) and $request->getParam('shared_id')
		}

		$this->view->assign('success', $success);
	}

}
