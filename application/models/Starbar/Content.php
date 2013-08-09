<?php

class Starbar_Content // note, this is model does not extend Record
{
	protected $_tableName = 'starbar_content';

	public static $allStarbarContent = null;
	public static $allStarbarContentKeys = null;

	static protected function _init () {
		if (self::$allStarbarContent === null) self::$allStarbarContent = Starbar_Content::getAllContent();
		if (self::$allStarbarContentKeys === null) self::$allStarbarContentKeys = array_keys(self::$allStarbarContent['0']);
	}

	/**
	 * Don't filter HTML!
	 */
	protected function _filter ($value, $property = '') {
		return $value;
	}

	static public function getByStarbarAndKey ($key, $starbarId = null, $tree = "^#^") {
		self::_init();

		$content = ""; // $content is returned when successful
		$starbarId = "" . $starbarId; // convert to string
		if (!$key) return;

		if ($key == "CONFIG_BASE_DOMAIN") {
			return BASE_DOMAIN;
		} else if ($key == "CONFIG_DEFAULT_SHARE_LINK" && APPLICATION_ENV != "production") {
			return "http://" . BASE_DOMAIN . "/";
		}

		// to ensure we don't infinitely recurse when we have sub-keys, track where we are.
		if (strpos($tree, "^#^" . $key . "^#^") !== false) {
			return "";
		}

		$tree .= $key . "^#^";

		if (in_array($key, self::$allStarbarContentKeys)) {
			if ($starbarId && isset(self::$allStarbarContent[$starbarId][$key]) && self::$allStarbarContent[$starbarId][$key])
				$content = self::$allStarbarContent[$starbarId][$key];

			// if key can't be find in the starbar's content (or if no starbar is specified), use the default content
			else $content = self::$allStarbarContent['0'][$key];

		} // } else { //should probably do something if the key isn't found, such as sending an email to admins to warn that content may be missing

		$content = self::getStringWithStarbarContent($content, $starbarId, $tree);

		return $content;
	}

	static public function getStringWithStarbarContent ($str, $starbarId, $tree = null) {
		self::_init();

		$subkeyMatches = [];
		$subkeyContent = [];

		if (preg_match_all("/%([a-zA-Z0-9_-]+)%/", $str, $subkeyMatches)) {
			foreach ($subkeyMatches[1] as $subkeyMatch) {
				if (!isset($subkeyContent[$subkeyMatch])) { // we already looked up this subkey
					$subkeyContent[$subkeyMatch] = self::getByStarbarAndKey($subkeyMatch, $starbarId, $tree);
				}
			}

			foreach ($subkeyContent as $subkey => $value) {
				$str = str_replace("%" . $subkey . "%", $value, $str);
			}
		}

		return $str;
	}

	/*
		Returns all starbar content in the format
		returned_array[STARBAR_ID][KEY] = "content"
		STARBAR_ID is a starbar_id (or 0 for the default content), and is always a string
		KEY is a content key

		[
			"0": [
				"CONFIG_BASE_DOMAIN": "local.saysollc.com"
				...
			],
			"3": [
				...
			],
			...
		]
	*/
	static public function getAllContent() {
		$cache = Api_Cache::getInstance('Starbar_Content', Api_Cache::LIFETIME_WEEK);

		if ($cache->test()) {

			$data = $cache->load();

		} else {

			$markupRootDir = realpath(APPLICATION_PATH . '/../markup');
			$data = json_decode(file_get_contents($markupRootDir . '/content.json'), true);

			$cache->save($data);
		}

		return $data;
	}
}

