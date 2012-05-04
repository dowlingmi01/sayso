(function () {
	setTimeout(function () { // Small delay to give page (and $SGQ variable) a chance to load
		if (!window.$SGQ) return;

		$SGQ = window.$SGQ;

		function notSet(someVar) {
			if (!someVar || someVar == 'undefined') return true;
			return false;
		}

		if (notSet($SGQ.srid) || notSet($SGQ.starbar_short_name) || notSet($SGQ.xdm_c) || notSet($SGQ.xdm_e) || notSet($SGQ.xdm_p)) return;

		$SGQ.in_iframe = (top !== self);

		if (!$SGQ.in_iframe && notSet($SGQ.snc)) return; // someone is visiting the survey directly instead of loading inside starbar

		loadFile('//' + $SGQ.base_domain + '/css/surveygizmo/surveys-' + $SGQ.starbar_short_name + '.css', 'css');
		if ($SGQ.size == "large") loadFile('//' + $SGQ.base_domain + '/css/surveygizmo/surveys-large-' + $SGQ.starbar_short_name + '.css', 'css');

		var maximumTimeToWait = 8000; // 8 seconds
		var timeBetweenChecks = 150;

		function waitForSQ(totalTimeWaitedSoFar) {
			if (totalTimeWaitedSoFar < maximumTimeToWait) {
				if (window.$SQ) {
					afterSQloads();
				} else {
					setTimeout(function() { waitForSQ(totalTimeWaitedSoFar+timeBetweenChecks); }, timeBetweenChecks);
				}
			}
		}

		waitForSQ(0);

		function everythingIsLoaded() {
			if (window.$SQ // sayso jQuery
				&& window.$SQ.sayso // sayso object
				&& window.$SQ.sayso.starbar // starbar object
				&& window.$SQ.sayso.starbar.user // user object
				&& $SGQ.loaded // survey DOM
				&& ((!$SGQ.in_iframe && window.sayso.starbar.loaded) || $SGQ.in_iframe) // if loading outside iframe, make sure starbar has loaded
				&& $SQ('.sg-footer-hook-2').css('text-align') == "right" // indicates that css is done loading
			) return true;
			else return false;
		}

		function afterSQloads () {
			var totalTimeWaitedSoFar = 0;
			$SQ.doTimeout('waitForEverything', timeBetweenChecks, function() {
			    if (everythingIsLoaded()) {
	    			if (!$SGQ.in_iframe && $SGQ.snc) { // User clicked save link from email
	    				// Open the survey on the starbar
						window.$SQ('#sayso-starbar').trigger('frameCommunication', ['openSurveyFromSave', {
							survey_id: $SGQ.surveyId,
							snc: $SGQ.snc,
							size: $SGQ.size
						}]);
						$SQ('.sayso_loading_container').hide();
	    				return false; // exit loop
					}

					var protocol = ('https:' == document.location.protocol ? 'https:' : 'http:');
	    			easyXDMParameters = "xdm_c=" + $SGQ.xdm_c + "&xdm_e=" + $SGQ.xdm_e + "&xdm_p=" + $SGQ.xdm_p;

					var redirectParameters = "?srid=" + $SGQ.srid + "&next_survey_id=" + $SGQ.next_survey_id + "&user_id=" + $SQ.sayso.starbar.user.id + "&user_key=" + $SQ.sayso.starbar.user.key + "&starbar_id=" + $SQ.sayso.starbar.id + "&" + easyXDMParameters + "#" + easyXDMParameters;
					if ($SQ('.sg-disqualify').length == 1) {
						self.location = protocol + "//" + $SGQ.base_domain + "/starbar/" + $SGQ.starbar_short_name + "/survey-disqualify" + redirectParameters;
						return;
					} else if ($SQ('.sg-progress-bar-full').length == 1) {
						self.location = protocol + "//" + $SGQ.base_domain + "/starbar/" + $SGQ.starbar_short_name + "/survey-complete" + redirectParameters;
						return;
					}

					// Loading complete!
					$SQ('.sg-wrapper').show();
					$SQ('.sayso_loading_container').hide();
			        return false; // exit doTimeout loop
			    }

			    totalTimeWaitedSoFar += timeBetweenChecks;

			    if (totalTimeWaitedSoFar > maximumTimeToWait) {
	    			alert("An error has occured while loading this survey. Please try again later.");
	    			return false; // exit doTimeout loop
				}

			    return true; // loop doTimeout
			});
		}
	}, 350);
})();
