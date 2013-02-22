<?php

class Starbar_Content extends Record
{
	protected $_tableName = 'starbar_content';

	public static $allStarbarContent = null;
	public static $allStarbarContentKeys = null;

	/**
	 * Don't filter HTML!
	 */
	protected function _filter ($value, $property = '') {
		return $value;
	}

	static public function getByStarbarAndKey ($key, $starbarId = null, $tree = "^#^") {
		$content = ""; // $content is returned when successful
		$keyId = 0;
		$subkeyMatches = array();
		$subkeyContent = array();

		if (!$key) return;

		// to ensure we don't infinitely recurse when we have sub-keys, track where we are.
		if (strpos($tree, "^#^" . $key . "^#^") !== false) {
			return "";
		}

		$tree .= $key . "^#^";

		if (self::$allStarbarContent === null) self::$allStarbarContent = Starbar_ContentCollection::getAllContent();
		if (self::$allStarbarContentKeys === null) self::$allStarbarContentKeys = Starbar_ContentKeyCollection::getAllKeys(true);;

		if (isset(self::$allStarbarContentKeys[$key])) $keyId = self::$allStarbarContentKeys[$key];

		if ($keyId) {
			if ($starbarId && isset(self::$allStarbarContent[$keyId][$starbarId]) && self::$allStarbarContent[$keyId][$starbarId]) $content = self::$allStarbarContent[$keyId][$starbarId];

			// if key can't be find in the starbar's content (or if no starbar is specified), look for the key in the default content
			else if (isset(self::$allStarbarContent[$keyId][0])) $content = self::$allStarbarContent[$keyId][0];

		} // } else { //should probably do something if the key isn't found, such as sending an email to admins to warn that content may be missing

		if (preg_match_all("/%([a-zA-Z0-9_-]+)%/", $content, $subkeyMatches)) {
			foreach ($subkeyMatches[1] as $subkeyMatch) {
				if (!isset($subkeyContent[$subkeyMatch])) { // we already looked up this subkey
					$subkeyContent[$subkeyMatch] = self::getByStarbarAndKey($subkeyMatch, $starbarId, $tree);
				}
			}

			foreach ($subkeyContent as $subkey => $value) {
				$content = str_replace("%" . $subkey . "%", $value, $content);
			}
		}

		return $content;
	}
}

