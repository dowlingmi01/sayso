<?php

class Markup {
	static public function getMarkup($userType, $app, $key, $starbarId = null) {
		// @todo implement caching?

		if (!$app || !$key) return false;

		// restrict allowed sections for public access
		if ($userType == "public") {
			if ($app != "webportal") return false; // currently there are only public endpoints inside the webportal
			$publicEndpoints = ["page", "header", "tour-start", "tour-polls" /* , etc. */];
			// if the requested markup isn't in the $publicEndpoints array, the user doesn't have access to this
			if (!in_array($key, $publicEndpoints)) return false;
		}

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
