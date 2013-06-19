<?php

class Markup {
	static public function getMarkup($userType, $app, $key, $starbarId = null) {
		// @todo implement some additional security
		// @todo implement caching?

		if (!$key || strpos($key, "..") !== false) return false;
		if ($app != "browserapp" && $app != "webportal") return false;

		// if we're here, the user has access to the requested markup (assuming it exists)
		$markupRootDir = realpath(APPLICATION_PATH . '/../markup');
		$starbarShortName = null;
		$markup = null;

		if ($starbarId) {
			$starbar = new Starbar();
			$starbar->loadData($starbarId);
			$starbarShortName = $starbar->short_name;
		}

		// look for markup for that starbar
		if ($starbarShortName && file_exists("$markupRootDir/$app/$starbarShortName/$key.html" ))
			$markup = file_get_contents("$markupRootDir/$app/$starbarShortName/$key.html");

		// look for generic markup
		if (!$markup && file_exists("$markupRootDir/$app/generic/$key.html"))
			$markup = file_get_contents("$markupRootDir/$app/generic/$key.html");

		if (!$markup) return false; // no markup found

		// Infuse starbar content keys and return the string (i.e. pull/convert the %STARBAR_CONTENT_KEY% instances)
		return Starbar_Content::getStringWithStarbarContent($markup, $starbarId);

	}


}
