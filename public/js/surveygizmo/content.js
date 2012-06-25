(function () {
	if (!window.$SGQ) return;

	function notSet(someVar) {
		if (!someVar || someVar == 'undefined') return true;
		return false;
	}

	if (notSet($SGQ.srid) || notSet($SGQ.starbar_short_name)) return;

	if (!sayso.in_iframe && notSet($SGQ.snc)) return; // someone is visiting the survey directly instead of loading inside starbar

	var cssSGQ = document.createElement('link');
	cssSGQ.rel = 'stylesheet';
	cssSGQ.href = '//' + $SGQ.base_domain + '/css/surveygizmo/surveys-' + $SGQ.starbar_short_name + '.css';
	document.body.appendChild(cssSGQ);
	
	if ($SGQ.size == "large") $SQ('head').append( $SQ('<link rel="stylesheet" type="text/css" />').attr('href', '//' + $SGQ.base_domain + '/css/surveygizmo/surveys-large-' + $SGQ.starbar_short_name + '.css') );

	var maximumTimeToWait = 8000; // 8 seconds
	var timeBetweenChecks = 150;

	function everythingIsLoaded() {
		return ( (sayso.in_iframe || window.sayso.starbar.loaded) // if loading outside iframe, make sure starbar has loaded
			&& $SQ('.sg-footer-hook-2').css('text-align') == "right" // indicates that css is done loading
		);
	}
	
	afterSQloads();
	
	function afterSQloads () {
		var totalTimeWaitedSoFar = 0;
		$SQ.doTimeout('waitForEverything', timeBetweenChecks, function() {
			if (everythingIsLoaded()) {
	    		if (!sayso.in_iframe && $SGQ.snc) { // User clicked save link from email
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

				var redirectParameters = "?srid=" + $SGQ.srid + "&next_survey_id=" + $SGQ.next_survey_id + "&user_id=" + $SQ.sayso.starbar.user.id + "&user_key=" + $SQ.sayso.starbar.user.key + "&starbar_id=" + $SQ.sayso.starbar.id + "&frame_id=" + $SGQ.xdm_c;
				if ($SQ('.sg-disqualify').length == 1) {
					window.location.href = protocol + "//" + $SGQ.base_domain + "/starbar/" + $SGQ.starbar_short_name + "/survey-disqualify" + redirectParameters;
					return;
				} else if ($SQ('.sg-progress-bar-full').length == 1) {
					window.location.href = protocol + "//" + $SGQ.base_domain + "/starbar/" + $SGQ.starbar_short_name + "/survey-complete" + redirectParameters;
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
})();
