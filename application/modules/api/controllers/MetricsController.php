<?php

require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Api_MetricsController extends Api_GlobalController
{

	public function init()
	{

	}

	public function indexAction()
	{
	}

	public function testAction () {
		return $this->_resultType(new Object(array('foo' => 'bar')));
	}

	public function pageViewSubmitAction () {
		$this->_validateRequiredParameters(array('user_id', 'user_key', 'starbar_id', 'url'));
		$metric = new Metrics_PageView();
		$metric->user_id = $this->user_id;
		$metric->starbar_id = $this->starbar_id;
		$metric->url = $this->url;
		$metric->save();

		return $this->_resultType($metric);

	}

	public function searchEngineSubmitAction () {
		$this->_validateRequiredParameters(array('user_id', 'user_key', 'starbar_id', 'query', 'type_id'));

		$metric = new Metrics_Search();
		$metric->user_id = $this->user_id;
		$metric->starbar_id = $this->starbar_id;
		$metric->search_engine_id = $this->type_id;
		$metric->query = $this->query;
		$metric->save();

		return $this->_resultType($metric);
	}

	public function socialActivitySubmitAction ()
	{
		$this->_validateRequiredParameters(array('user_id', 'user_key', 'starbar_id', 'type_id'));

		$metric							 = new Metrics_SocialActivity();
		$metric->user_id					= $this->user_id;
		$metric->starbar_id				 = $this->starbar_id;
		$metric->social_activity_type_id	= $this->type_id;

		switch(intval($this->type_id))
		{
			case 1:
				// facebook "Like"
				$metric->url		= $this->url;
				$metric->content	= 'Facebook\'s "Like" button clicked';
				break;
			default:
				// currently it is twitter only
				$metric->url		= $this->url;
				$metric->content	= $this->content;
				break;
		}

		$metric->save();

		return $this->_resultType($metric);
	}

	public function trackStudyAdViewsAction () {

		$this->_validateRequiredParameters(array('user_id', 'user_key', 'starbar_id', 'url', 'study_ad_views'));

		$studyAdViews = json_decode($this->study_ad_views, true);
		$studyAd = new Study_Ad();

		foreach ($studyAdViews as $studyAdId) {
			if ($studyAd->loadDataByUniqueFields(array("id" => $studyAdId))) { // Check if study_ad record exists
				$studyAdUserMap = new Study_AdUserMap();
				$studyAdUserMap->user_id = $this->user_id;
				$studyAdUserMap->starbar_id = $this->starbar_id;
				$studyAdUserMap->study_ad_id = $studyAdId;
				$studyAdUserMap->url = $this->url;
				$studyAdUserMap->type = 'view';
				$studyAdUserMap->save();
			}
		}

		return $this->_resultType(true);
	}

	public function trackStudyAdClicksAction () {

		$this->_validateRequiredParameters(array('user_id', 'user_key', 'starbar_id', 'url', 'study_ad_clicks'));

		$studyAdClicks = json_decode($this->study_ad_clicks, true);
		$studyAd = new Study_Ad();

		foreach ($studyAdClicks as $studyAdId) {
			if ($studyAd->loadDataByUniqueFields(array("id" => $studyAdId))) { // Check if study_ad record exists
				$studyAdUserMap = new Study_AdUserMap();
				$studyAdUserMap->user_id = $this->user_id;
				$studyAdUserMap->starbar_id = $this->starbar_id;
				$studyAdUserMap->study_ad_id = $studyAdId;
				$studyAdUserMap->url = $this->url;
				$studyAdUserMap->type = 'click';
				$studyAdUserMap->save();
			}
		}

		return $this->_resultType(true);
	}

	public function trackAdViewsAction () {

		$this->_validateRequiredParameters(array('user_id', 'user_key', 'starbar_id', 'cell_activity'));

		$cellActivity= json_decode($this->cell_activity, true);

		// example JSON:
		// {"25":{"tagViews":[],"creativeViews":[]},"26":{"tagViews":[],"creativeViews":[]},"23":{"tagViews":[],"creativeViews":[]},"24":{"tagViews":[],"creativeViews":[]}}

		foreach ($cellActivity as $cellId => $data) {
			foreach ($data['tagViews'] as $tagId) {
				$tagView = new Metrics_TagView();
				$tagView->user_id = $this->user_id;
				$tagView->starbar_id = $this->starbar_id;
				$tagView->cell_id = $cellId;
				$tagView->tag_id = $tagId;
				$tagView->save();
			}
			foreach ($data['creativeViews'] as $creativeId) {
				$creativeView = new Metrics_CreativeView();
				$creativeView->user_id = $this->user_id;
				$creativeView->starbar_id = $this->starbar_id;
				$creativeView->cell_id = $cellId;
				$creativeView->creative_id = $creativeId;
				$creativeView->save();
			}
		}

		return $this->_resultType(true);
	}

	public function trackClickThruAction () {
		$this->_validateRequiredParameters(array('user_id', 'user_key', 'starbar_id', 'url', 'url_segment', 'type', 'type_id'));

		switch ($this->type) {
			case 'creative' :
				$sql = 'SELECT v.id FROM metrics_creative_view v WHERE v.creative_id = ? AND v.user_id = ? ORDER BY v.created DESC LIMIT 1';
				$result = Db_Pdo::fetch($sql, $this->type_id, $this->user_id);
				$metric = new Metrics_CreativeClickThru();
				$metric->metrics_creative_view_id = $result['id'];
				$metric->url = $this->url;
				$metric->save();
				break;
			case 'campaign' :
				$sql = 'SELECT v.id FROM metrics_tag_view v WHERE v.tag_id = ? AND v.user_id = ? ORDER BY v.created DESC LIMIT 1';
				$result = Db_Pdo::fetch($sql, $this->type_id, $this->user_id);
				$metric = new Metrics_TagClickThru();
				$metric->metrics_tag_view_id = $result['id'];
				$metric->url = $this->url;
				$metric->save();
				break;
		}

		return $this->_resultType(true);
	}
}


