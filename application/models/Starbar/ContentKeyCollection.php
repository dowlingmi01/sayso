<?php

class Starbar_ContentKeyCollection extends RecordCollection
{
	static public function getAllKeys($keyAsIndex = false) {
		$cacheName = 'Starbar_Content_Keys_' . ($keyAsIndex ? "Key_Index" : "Id_Index");
		$cache = Api_Cache::getInstance($cacheName, Api_Cache::LIFETIME_WEEK);

		if ($cache->test()) {

			$data = $cache->load();

		} else {

			$data = array();

			$sql = "
				SELECT id, title
				FROM starbar_content_key
			";

			$rawData = Db_Pdo::fetchAll($sql);

			foreach ($rawData as $row) {
				if ($keyAsIndex) $data[$row['title']] = (int) $row['id'];
				else $data[(int) $row['id']] = $row['title'];
			}

			$cache->save($data);
		}

		return $data;
	}
}
