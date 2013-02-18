<?php

require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Api_ContentController extends Api_GlobalController
{
	public function init()
	{

	}

	public function saveStarbarContentAction() {
		$result = array();

		$request = $this->getRequest();
		$keyId = (int) $request->getParam("key_id", 0);
		$keyTitle = $request->getParam("key_title");

		if (!$keyId && !$keyTitle) return $this->_resultType(false);

		$starbarContentKey = new Starbar_ContentKey();
		if ($keyId) $starbarContentKey->loadData($keyId);
		$starbarContentKey->title = $keyTitle;
		$starbarContentKey->save();

		if (!$starbarContentKey->id) return $this->_resultType(false);

		$result['key_id'] = $starbarContentKey->id;
		$result['key_title'] = $starbarContentKey->title;

		$sql = "SELECT *
				FROM starbar
				WHERE id > 2
				";
		$starbars = Db_Pdo::fetchAll($sql);

		array_unshift($starbars, array('id' => '-1', 'label' => 'Default'));

		foreach ($starbars as $starbar) {
			$starbarId = $starbar['id'];

			$starbarContent = new Starbar_Content();
			$starbarContent->loadDataByUniqueFields(array("starbar_id" => ($starbarId > 0 ? $starbarId : null), "starbar_content_key_id" => $starbarContentKey->id));
			$starbarContent->content = $request->getParam('content_' . $starbarId);
			$starbarContent->save();

			$result["content_" . $starbarId] = $starbarContent->content;
		}

		Api_Cache::quickRemove('Starbar_Content_Starbar_Id_Index');
		Api_Cache::quickRemove('Starbar_Content_Content_Key_Index');
		Api_Cache::quickRemove('Starbar_Content_Keys_Id_Index');
		Api_Cache::quickRemove('Starbar_Content_Keys_Key_Index');

		// hack
		$result = array("status" => "success", "data" => $result);
		echo json_encode($result);
		exit;
	}
}


