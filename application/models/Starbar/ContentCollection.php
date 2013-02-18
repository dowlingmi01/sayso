<?php

class Starbar_ContentCollection extends RecordCollection
{
	/*
		Returns all starbar content.
		$starbarId specifies the starbar. leave null to get content for all starbars. If specified, the resulting data looks like:
			array[$keyId] = "content"
		for all the content for a single starbar (set to -1 to get default content only)

		$useStarbarIdAsIndex is only used if $starbarId is NOT set (or set to 0 or false).
		if $useStarbarIdAsIndex is true, content for all starbars is returned in the format:
			array[$starbarId][$keyId] = "content"
		otherwise the default is:
			array[$keyId][$starbarId] = "content"
	*/
	static public function getAllContent($starbarId = null, $useStarbarIdAsIndex = null) {
		if ($starbarId) {
			$temp = self::getAllContent(null, true);
			return (isset($temp[$starbarId]) ? $temp[$starbarId] : null);
		}

		$cacheName = 'Starbar_Content_' . ($useStarbarIdAsIndex ? 'Starbar_Id_Index' : 'Content_Key_Index');
		$cache = Api_Cache::getInstance($cacheName, Api_Cache::LIFETIME_WEEK);

		if ($cache->test()) {

			$data = $cache->load();

		} else {

			$data = array();

			$selectFields = "sck.id AS key_id, sc.content, sc.starbar_id";
			$orderFields = "sck.title, sc.content";
			$whereClause = "";

			if ($useStarbarIdAsIndex) $orderFields = "sc.starbar_id, " . $orderFields;

			$sql = "
				SELECT " . $selectFields . "
				FROM starbar_content sc
				INNER JOIN starbar_content_key sck
					ON sc.starbar_content_key_id = sck.id
				" . $whereClause . "
				ORDER BY " . $orderFields . "
			";

			$rawData = Db_Pdo::fetchAll($sql);

			$previousKeyId = -1;
			$previousStarbarId = -1;

			foreach ($rawData as $row) {
				$currentKeyId = (int) $row['key_id'];
				$currentStarbarId = (int) $row['starbar_id'];

				if ($useStarbarIdAsIndex) {
					if ($currentStarbarId != $previousStarbarId) {
						$data[$currentStarbarId] = array();
						$previousStarbarId = $currentStarbarId;
					}

					$data[$currentStarbarId][$currentKeyId] = $row['content'];
				} else {
					if ($currentKeyId != $previousKeyId) {
						$data[$currentKeyId] = array();
						$previousKeyId = $currentKeyId;
					}

					$data[$currentKeyId][$currentStarbarId] = $row['content'];
				}
			}

			$cache->save($data);
		}

		return $data;
	}
}
