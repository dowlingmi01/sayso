<?php

class Starbar_Content extends Record
{
	protected $_tableName = 'starbar_content';

	public static $allStarbarContent = null;
	public static $allStarbarContentKeys = null;

	static protected function _init () {
		if (self::$allStarbarContent === null) self::$allStarbarContent = Starbar_ContentCollection::getAllContent();
		if (self::$allStarbarContentKeys === null) self::$allStarbarContentKeys = Starbar_ContentKeyCollection::getAllKeys(true);
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
		$keyId = 0;

		if (!$key) return;

		// to ensure we don't infinitely recurse when we have sub-keys, track where we are.
		if (strpos($tree, "^#^" . $key . "^#^") !== false) {
			return "";
		}

		$tree .= $key . "^#^";

		if (isset(self::$allStarbarContentKeys[$key])) $keyId = self::$allStarbarContentKeys[$key];

		if ($keyId) {
			if ($starbarId && isset(self::$allStarbarContent[$keyId][$starbarId]) && self::$allStarbarContent[$keyId][$starbarId]) $content = self::$allStarbarContent[$keyId][$starbarId];

			// if key can't be find in the starbar's content (or if no starbar is specified), look for the key in the default content
			else if (isset(self::$allStarbarContent[$keyId][0])) $content = self::$allStarbarContent[$keyId][0];

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

}

