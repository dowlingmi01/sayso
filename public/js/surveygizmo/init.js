(function () {
	setTimeout(function () { // Small delay to give page (and $SGQ variable) a chance to load
		if (!window.$SGQ) return;

		$SGQ = window.$SGQ;

		// survey_response_id -- since we will use this field to filter later, follow SG's preference to not use underscores in url vars
		$SGQ.srid = loadVariable("srid");
		// to redirect to later, e.g. "hellomusic"
		$SGQ.starbar_short_name = loadVariable("starbar_short_name");
		// easyXDM vars
		$SGQ.xdm_c = loadVariable("xdm_c");
		$SGQ.xdm_e = loadVariable("xdm_e");
		$SGQ.xdm_p = loadVariable("xdm_p");

		$SGQ.next_survey_id = loadVariable("next_survey_id");
		$SGQ.size = loadVariable("size");

		if (!$SGQ.srid || !$SGQ.starbar_short_name || !$SGQ.xdm_c || !$SGQ.xdm_e || !$SGQ.xdm_p) return;

		$SGQ.snc = loadVariable("snc"); // For resuming survey from save
		$SGQ.in_iframe = (top !== self);

		if (!$SGQ.in_iframe && !$SGQ.snc) return; // someone is visiting the survey directly instead of loading inside starbar

		loadFile('//' + $SGQ.base_domain + '/css/surveygizmo/surveys-' + $SGQ.starbar_short_name + '.css', 'css');
		if ($SGQ.size == "large") loadFile('//' + $SGQ.base_domain + '/css/surveygizmo/surveys-large-' + $SGQ.starbar_short_name + '.css', 'css');

		function SQIsLoaded() {
			if (jQuery && $ //jQuery on SG via googleapi servers
				&& window.$SQ //starbar
			) return true;
			else return false;
		}

		function everythingIsLoaded() {
			if (jQuery && $ // jQuery on SG via googleapi servers
				&& window.$SQ // sayso jQuery
				&& window.$SQ.sayso // sayso object
				&& window.$SQ.sayso.starbar // starbar object
				&& window.$SQ.sayso.starbar.user // user object
				&& $SGQ.loaded // survey DOM
				&& ((!$SGQ.in_iframe && window.sayso.starbar.loaded) || $SGQ.in_iframe) // if loading outside iframe, make sure starbar has loaded
				&& $('.sg-footer-hook-2').css('background-color') == "rgb(243, 243, 244)" // i.e. "#F3F3F4", which indicates that css is done loading
			) return true;
			else return false;
		}

		var maximumTimeToWait = 8000; // 8 seconds
		var timeBetweenChecks = 150;
		var totalTimeWaitedSoFar = 0;

		$.doTimeout('waitForSQ', timeBetweenChecks, function() {
			if (SQIsLoaded()) {
				afterSQloads();
				return false;
			}

		    totalTimeWaitedSoFar += timeBetweenChecks;

		    if (totalTimeWaitedSoFar > maximumTimeToWait) {
	    		alert("An error has occured while loading this survey. Please try again later.");
	    		return false; // exit doTimeout loop
			}

		    return true; // loop doTimeout
		});

		function afterSQloads () {
			totalTimeWaitedSoFar = 0;
			$.doTimeout('waitForEverything', timeBetweenChecks, function() {
			    if (everythingIsLoaded()) {
	    			if (!$SGQ.in_iframe && $SGQ.snc) { // User clicked save link from email
	    				// Open the survey on the starbar
						window.$SQ('#sayso-starbar').trigger('frameCommunication', ['openSurveyFromSave', {
							survey_id: $SGQ.surveyId,
							snc: $SGQ.snc,
							size: $SGQ.size
						}]);
						$('#sg-wrapper').show();
						$('.sayso_loading_container').hide();
	    				return false; // exit loop
					}

					var protocol = ('https:' == document.location.protocol ? 'https:' : 'http:');
	    			easyXDMParameters = "xdm_c=" + $SGQ.xdm_c + "&xdm_e=" + $SGQ.xdm_e + "&xdm_p=" + $SGQ.xdm_p;

					var redirectParameters = "?srid=" + $SGQ.srid + "&next_survey_id=" + $SGQ.next_survey_id + "&user_id=" + $SQ.sayso.starbar.user.id + "&user_key=" + $SQ.sayso.starbar.user.key + "&auth_key=" + $SQ.sayso.starbar.authKey + "&" + easyXDMParameters + "#" + easyXDMParameters;
					if ($('.sg-disqualify').length == 1) {
						self.location = protocol + "//" + $SGQ.base_domain + "/starbar/" + $SGQ.starbar_short_name + "/survey-disqualify" + redirectParameters;
						return;
					} else if ($('.sg-progress-bar-full').length == 1) {
						self.location = protocol + "//" + $SGQ.base_domain + "/starbar/" + $SGQ.starbar_short_name + "/survey-complete" + redirectParameters;
						return;
					}

					// Loading complete!
					$('.sayso_loading_container').hide();
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
