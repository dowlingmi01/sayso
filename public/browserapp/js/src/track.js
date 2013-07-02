sayso.module.track = (function(config, util, $, comm, api, state) { return function(inIframe, topLocation, adTargets) {
	function legacyApiCall(action, data, callback) {
		data.starbar_id = state.state.starbar.id;
		api.doRequest( {
			action_class: 'LegacyApi', action: 'call', legacy_class: 'Metrics',
			legacy_action: action, parameters: data
		}, callback);
	}

	// ADjuster blacklist

	var trackerBlackList = [/saysollc\.com/];
	for (var i = 0, ln = trackerBlackList.length; i < ln; i++)
	{
		if (trackerBlackList[i].test(config.location.href))
		{
			return;
		}
	}

	/**
	 * Helper function for recording behaviors on the server
	 */
	var behaviorTracker = {

		pageView: function () {
			legacyApiCall( 'pageViewSubmit',
				{ url : encodeURIComponent(config.location.protocol + '//' + config.location.host + config.location.pathname) }
			);
		},

		videoView: function (type, id) {
			this.videoId = id;
			legacyApiCall( 'videoViewSubmit',
				{
					video_type : type,
					video_id : id,
					video_url : config.location.href,
					page_url : topLocation.href
				}
			);
		},

		search: function (data) {
			legacyApiCall( 'searchEngineSubmit', data );
		},

		// social activity

		socialActivity: function (url, content, type_id) {
			legacyApiCall('socialActivitySubmit',
				{
					type_id : type_id,
					url : url,
					content : content
				}
			);
		}

	};

	// Behavioral tracking

	// ================================================================
	// Page View

	if (!inIframe) {
		behaviorTracker.pageView();
	}

	// ================================================================
	// Video View
	{
		var m = config.location.href.match(/youtube\..*\/watch.*[?&]v=([\w\-]{11})/);
		if( m )
			behaviorTracker.videoView('youtube', m[1] );
		else if( m = config.location.href.match(/youtube\..*\/embed\/([\w\-]{11})/) )
			behaviorTracker.videoView('youtube', m[1] );
		else if( config.location.href.match(/youtube\./) )
			$.doTimeout(3000, function checkForVideoPlayer() {
				var vid;
				if( behaviorTracker.videoId )
					return false;
				else if( (vid = $('div.player-root[data-video-id]')).length ) {
					vid = vid.attr('data-video-id');
					if( vid.length === 11 ) {
						behaviorTracker.videoView('youtube', vid);
						return false;
					}
				}
				return true;
			});
	}
	
	// ================================================================
	// Search

	if (!inIframe) {

		var searchType = 0,
			searchRegex = '';

		var googleEngineRegexp = /google(\..{2,3})+(\..{2,3})?\//;

		if (config.location.href.match('bing.com/search'))
		{
			searchType = 1; // bing (these ids match lookup_search_engines table)
			searchRegex = /q=([^&]+)&/g;
		}
		else if (googleEngineRegexp.test(config.location.href))
		{
			searchType = 2; // google
			searchRegex = /(?:\?|&)q=([^&]+)/;
		}
		else if (config.location.href.match('search.yahoo.com'))
		{
			searchType = 3; // yahoo
			searchRegex = /[\?&]?p=([^&]+)&/g;
		}

		if (searchType)
		{
			var data = {
				type_id : searchType
			};

			var searchQueryArray = searchRegex.exec(config.location.href);
			if (searchQueryArray && searchQueryArray.length > 1)
			{
				var searchQuery = searchQueryArray[1];
				if (searchQuery) {
					data.query = searchQuery;
					behaviorTracker.search(data);
				}
			}
			else
			{
				util.log('On search page, but no query found');
			}

			// We are in Google, let's monitor the query field
			if(searchType === 2)
			{
				// remeber initial value in search field
				var lastQueryValue  = $('input[name=q]').val();
				// the above is changed and remains so validInterval ms
				var validInterval   = 2000;
				// start counting time at 0
				var startInterval   = 0;
				// poll each checkInterval ms
				var checkInterval   = 100;
				// did we send stats before?
				// yes, we sent them at the page load already...
				var statsSent	   = true;
				// do poll
				$.doTimeout(checkInterval, function()
				{
					// any changes
					var currentQueryValue = $('input[name=q]').val();

					// yes, reset all
					if(currentQueryValue !== lastQueryValue)
					{
						lastQueryValue  = currentQueryValue;
						startInterval   = 0;
						statsSent	   = false;
					}
					else
					{
						// increment the start and check
						startInterval += checkInterval;
						if(startInterval >= validInterval)
						{
							// no stats sent? send it!
							if(!statsSent && currentQueryValue)
							{
								// send now asynchronously...
								data.query = currentQueryValue;
								behaviorTracker.search(data);
								// but set the check synchronously to avoid repeating...
								statsSent = true;
							}
							// reset
							startInterval = 0;
						}
					}
					return true;
				});
			}
		}
	}

	// ================================================================
	// Tweets
	var tweet;

	// popup/x-domain Tweet tracking
	if (config.location.href.match('twitter.com/intent')) {

		var tweetUrl = decodeURIComponent(/(?:\?|&)url=([^&]+)/.exec(config.location.search)[1]);
		tweet = $('#status').val();
		$('#status').keyup(function () {
			// on every key event we capture the full contents. ensures that
			// the tweet isn't removed by Twitter before we grab it
			tweet = $(this).val();
		});
		// use mousedown, not click. click is not reliable because the window
		// is closed very quickly after submitting the tweet which kills the ajax call
		$('#update-form input.submit').mousedown(function () {
			behaviorTracker.socialActivity(tweetUrl, tweet, 2);
			$(this).unbind('mousedown');
		});
	// Tweet tracking on Twitter.com
	} else if (config.location.hostname.match('twitter.com') && $('div.tweet-box textarea').length) {

		tweet = '';

		// append to what is already bound...
		$('div.tweet-box textarea').bind('keyup', function()
		{
			// since there is a race condition between
			// when our click event is fired and Twitter removes
			// the content of the tweet box, then we just
			// continuously capture the contents here
			tweet = $(this).val();
		});

		// append to what is already bound...
		$('div.tweet-box div.tweet-button-sub-container').bind('click', function(e)
		{
			try
			{
				behaviorTracker.socialActivity(config.location.href, tweet, 2);
				e.preventDefault();
				tweet = '';
			}
			catch(ex)
			{
				util.log('Exception: '+ ex.getMessage());
			}
		});
	}

	// ================================================================
	// Facebook Like

	if (inIframe) {
		var comment;
		if (config.location.href.match('facebook.com/sharer/sharer')) {

			var textArea = $('textarea').first(),
				url = decodeURIComponent(/(?:\?|&)u=([^&]+)/.exec(config.location.search)[1]);
			comment = textArea.val();

			textArea.keyup(function () {
				comment = $(this).val();
			});

			$('input[name=share]').click(function () {
				if (comment.length && comment !== 'Write Something...') {
					behaviorTracker.socialActivity(url, comment, 1);
				}
			});
		} else if (config.location.href.match('facebook.com/plugins/comment_widget_shell')) {
			util.log('In comment widget!!'); // can't get this working, iframe loads into a hidden div ?
			comment = $('textarea.connect_comment_widget_full_input_textarea').val();
			util.log(comment);
		} else if (config.location.href.match('facebook.com/plugins/like')) {
			$('.pluginConnectButton button').click(function () {
				var likedUrl = decodeURIComponent(/href=([^&]*)/g.exec(config.location.search)[1]);
				behaviorTracker.socialActivity(likedUrl, '', 1);
			});
		}
	}

	// ================================================================
	// ADjuster

//	if (!sayso.flags.match('adjuster_ads')) return; // globally disabled ad detection/replacement

	util.log('ADjuster ad handling enabled');

	if (!adTargets) adTargets = {};

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
		if( studyAd.existing_ad_type === "video" ) {
			if( !behaviorTracker.videoId || behaviorTracker.videoId != studyAd.existing_ad_tag )
				return;
		} else {
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
