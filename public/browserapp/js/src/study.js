sayso.module.study = (function(config, util, $, comm, api, state) { return function(inIframe) {
	function legacyApiCall(action, data, callback) {
		data.starbar_id = state.state.starbar.id;
		api.doRequest( {
			action_class: 'LegacyApi', action: 'call', legacy_class: 'Metrics',
			legacy_action: action, parameters: data
		}, callback);
	}

	// ================================================================
	// ADjuster

//	if (!sayso.flags.match('adjuster_ads')) return; // globally disabled ad detection/replacement

	util.log('ADjuster ad handling enabled');

	var adTargets = state.state.adTargets || {};

	var studyAdClicks = [];

	if (!inIframe) {
		// ADjuster Click-Thrus ------------------------

		util.log('Ad Targets: ', adTargets);
		// { creative12 : { urlSegment : 'foo/diamonds', type : 'creative', type_id : 12 }, campaign234 : { etc
		for (var key in adTargets) {
			var viewedStudyAd = adTargets[key];
			if (config.location.href.indexOf(viewedStudyAd.ad_target) > -1) {
				studyAdClicks.push(viewedStudyAd.id);
			}
		}

		if (studyAdClicks.length > 0) {
			// click thrus!
			legacyApiCall('trackStudyAdClicks',
				{
					url : config.location.href,
					study_ad_clicks : JSON.stringify(studyAdClicks)
				},
				function () {
					comm.request('delete-ad-targets', studyAdClicks);
				}
			);
		}
	}

	// ADjuster Setup Studies ------------------------

	var studyAdViews = [];
	var sessionAdViews = [];
	var adsFound = 0;
	var replacements = 0;
	var numberOfAdChecks = 0;

	var studyAds = state.state.studies;

	function processStudyAds () {
		// non-existent OR expired studies

		var studyAd = null;

		if (!inIframe && numberOfAdChecks === 0) util.log('Current Study Ads: ', studyAds);

		// study ads
		for (var a in studyAds) {
			studyAd = studyAds[a];
			if (
				studyAd &&
				studyAd.existing_ad_tag && // there's a tag to search for
				($.inArray(studyAd.id, sessionAdViews) === -1) && // ad hasn't already been viewed in this session
				topLocation.host.match(studyAd.existing_ad_domain) // we're on the right domain
			) {
				processStudyAd(studyAd);
			}
		} // study ads

		studyAdsProcessingComplete();
	}

	$.doTimeout('process-study-ads', 3000, function () {
		if (numberOfAdChecks < 5) {
			processStudyAds();
			numberOfAdChecks++;
		} else {
			// Stop the loop
			return false;
		}
		return true;
	});
	$.doTimeout('process-study-ads', true); // run once immediately

	/**
	 * Process each tag including ad detection and/or replacement
	 * - running this method assumes a domain matches for the current URL
	 * - this function inherits currentActivity for the current cell
	 */
	function processStudyAd (studyAd) {
		// log('studyAd.existing_ad_tag: ');
		// log(studyAd.existing_ad_tag);

		var jTag = false;
		var jTagContainer = false;
		if( studyAd.existing_ad_type !== "video" ) {
			if (studyAd.existing_ad_type === "image") {
				jTag = $('img[src*="' + studyAd.existing_ad_tag + '"]');
			} else if (studyAd.existing_ad_type === "flash") {
				jTag = $('embed[src*="' + studyAd.existing_ad_tag + '"]');
				if (!jTag || !jTag.length) {
					// Try to search (using jQuery) for the param element (though on IE and possibly other browsers, params are not in the DOM).
					jTag = $('param[name="movie"][value*="'+studyAd.existing_ad_tag+'"]');

					// If flash is still not found on this page, try looking inside all the <object> tags' children (i.e. the params)
					if (!jTag || !jTag.length) {
						$('object param[name="movie"]').each(function() {
							if ($(this).attr('value').indexOf(studyAd.existing_ad_tag) > -1) {
								jTag = $(this);
								jTagContainer = jTag.parent();
								// Match found, need need to search any more
								return false;
							} else {
								// Go to next object tag
								return true;
							}
						});
					}
				}
			} else if (studyAd.existing_ad_type === "facebook") {
				jTag = $('div[id*="' + studyAd.existing_ad_tag + '-id_"]');
			}


			if (!jTag || !jTag.length) {
				util.log('No Matches');
				return;
			}

			util.log('Match', jTag);

			if (!jTagContainer) jTagContainer = jTag.parent();

			if (jTagContainer.is('object')) {
				// If we found a param tag inside an <object> tag, we want the parent of *that*
				jTagContainer = jTagContainer.parent();
			}

			jTagContainer.css('position', 'relative');
		}

		// tag exists
		adsFound++;

		if (studyAd.type === "creative") { // ADjuster Creative ------------

			replacements++;

			// replace ad
			var adWidth = jTag.innerWidth();
			var adHeight = jTag.innerHeight();
			var newTag = $(document.createElement('div'));
			newTag.css({
				'width': adWidth+'px',
				'height': adHeight+'px',
				'overflow': 'hidden',
				'display': 'block'
			});
			switch (studyAd.replacement_ad_type) {
				case "image":
					newTag.html('<a id="sayso-adcreative-'+studyAd.id+'" href="'+studyAd.ad_target+'" target="_new"><img src="'+studyAd.replacement_ad_url+'" alt="'+studyAd.replacement_ad_title+'" title="'+studyAd.replacement_ad_title+'" border=0 /></a>');
					break;
				case "flash":
					newTag.html(''); // @todo, insert <object><param><param><embed></object> etc. for flash ads
					break;
				case "facebook":
					newTag.html(' \
						<div class="_24n _24y"> \
							<div class="uiSelector inlineBlock emu_x emuEventfad_hide _24x uiSelectorRight"></div> \
							<div class="title"><a class="forceLTR" href="'+studyAd.ad_target+'" target="_blank">'+studyAd.replacement_ad_title+'</a></div> \
							<div class="clearfix image_body_block"> \
								<a class="emuEvent1 _24x image fbEmuImage _8o _8s lfloat" href="'+studyAd.ad_target+'" target="_blank"> \
									<img class="img" src="'+studyAd.replacement_ad_url+'" alt=""> \
								</a> \
								<div class="_8m"><div class="body"><a class="forceLTR emuEvent1 _24x" href="'+studyAd.ad_target+'" target="_blank">'+studyAd.replacement_ad_description+'</a></div></div> \
							</div> \
							<div class="inline"><div class="action"></div></div> \
						</div> \
					');
					break;
			}
			jTagContainer.html('').append(newTag);
			jTagContainer.css({
				'width': adWidth+'px',
				'height': adHeight+'px',
				'left': 0,
				'top': 0
			});

			// record view of the creative
			studyAdViews.push(studyAd.id);
			sessionAdViews.push(studyAd.id);

			util.log('ADjuster: Creative Replacement');

		} else { // ADjuster Campaign ------------------------

			// record view of the campaign
			studyAdViews.push(studyAd.id);
			sessionAdViews.push(studyAd.id);

			util.log('ADjuster: Campaign View');

		}

		// update list of ad targets so we can later verify click throughs
		// see Page View section above where this is checked

		if( studyAd.ad_target )
			comm.request('add-ad-target', studyAd);
	}

	// Track ad views!

	// load timer is used so that asynchronous JS does not run this Ajax call too early
	// and to prevent the need for deeply nested callbacks

	function studyAdsProcessingComplete() {
		if (adsFound) {
			util.log('Ads matched ' + adsFound + '. Ads replaced ' + replacements);
			legacyApiCall('trackStudyAdViews',
				{
					url : topLocation.href,
					study_ad_views : JSON.stringify(studyAdViews)
				},
				function () {
					studyAdViews = [];
					adsFound = 0;
					replacements = 0;
				}
			);
		} else {
			util.log('No ads match');
		}
	}

};})(sayso.module.config, sayso.module.util, jQuery, sayso.module.comm, sayso.module.api, sayso.module.state)
;
