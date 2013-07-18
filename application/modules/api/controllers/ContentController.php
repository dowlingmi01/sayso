<?php

require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Api_ContentController extends Api_GlobalController
{
	public function init()
	{

	}

	public function saveStarbarContentAction() {
		$request = $this->getRequest();
		$key = $request->getParam("key");

		if (!$key) return $this->_resultType(false);

		$allContent = Starbar_Content::getAllContent();

		$newKey = $request->getParam("new_key");

		$sql = "SELECT id
				FROM starbar
				WHERE id > 2
				";
		$starbarIds = Db_Pdo::fetchColumn($sql);
		array_unshift($starbarIds, '0');

		foreach($starbarIds as $starbarId) {
			unset($allContent[(int) $starbarId][$key]);
		}

		if ($key != $newKey && $newKey) {
			$key = $newKey;
		}

		if ($key == "new") return $this->_resultType(false); // when user leaves key blank

		$resultData = [ "key" => $key ];

		foreach($starbarIds as $starbarId) {
			$content = $request->getParam('content_' . $starbarId, "");
			if ($starbarId === '0' || $content) // starbar '0' contains all the keys
				$allContent[(int) $starbarId][$key] = $content;

			$resultData["content_" . $starbarId] = $content;
		}

		foreach($allContent as &$subArray) {
			ksort($subArray);
		}

		$jsonedContent = json_encode($allContent, JSON_PRETTY_PRINT);
		$markupRootDir = realpath(APPLICATION_PATH . '/../markup');
		file_put_contents($markupRootDir . '/content.json', $jsonedContent);

		Api_Cache::quickRemove('Starbar_Content');

		// hack
		$result = [ "status" => "success", "data" => $resultData ];
		echo json_encode($result);
		exit;
	}
}


