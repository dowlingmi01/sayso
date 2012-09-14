// available to this script:
//	 $SQ.jsLoadTimer
// (see sayso-state.js)

$SQ(function () {

	var starbar = $SQ.sayso.starbar;

	// Check required params

	if (!starbar.id || !starbar.user.id || !starbar.user.key) return;

	// ADjuster blacklist

	var trackerBlackList = [/saysollc\.com/];
	for (var i = 0, ln = trackerBlackList.length; i < ln; i++)
	{
		if (trackerBlackList[i].test(sayso.location.href))
		{
			return;
		}
	}

	// setup

	var log = sayso.log,
		warn = sayso.warn,
		inIframe = sayso.in_iframe;

	var ajax = function (options) {
		options.data = $SQ.extend(options.data || {}, {
			user_id : starbar.user.id,
			user_key : starbar.user.key,
			starbar_id : starbar.id,
			renderer : 'json'
		});
		options.dataType = 'json';
		options.url = 'http:' + options.url;
		return forge.request.ajax(options);
	};

	if (inIframe) log('iFrame', sayso.location.host);

	/**
	 * Helper function for recording behaviors on the server
	 */
	var behaviorTracker = new function () {

		this.pageView = function () {
			ajax({
				url : '//' + sayso.baseDomain + '/api/metrics/page-view-submit',
				data : {
					url : encodeURIComponent(sayso.location.protocol + '//' + sayso.location.host + sayso.location.pathname)
				},
				success : function (response) {}
			});
		};

		this.search = function (url, data) {
			ajax({
				url	 : url,
				data	: data,
				success : function (response) {
					log('Behavioral: Search');
				}
			});
		};

		// social activity

		// see KRL for Facebook Like logic

		this.socialActivity = function (url, content, type_id) {
			ajax({
				url : '//' + sayso.baseDomain + '/api/metrics/social-activity-submit',
				data : {
					type_id : type_id,
					url : url,
					content : content
				},
				success : function (response) {
					log('Behavioral: ' + (type_id === 1 ? 'Facebook' : 'Twitter'));
				}
			});
		};

	};

	// Behavioral tracking

	// ================================================================
	// Page View

	if (!inIframe) {
		behaviorTracker.pageView();
	}

	// ================================================================
	// Search

	if (!inIframe) {

		var searchType = 0,
			searchRegex = '';

		var googleEngineRegexp = /google(\..{2,3})+(\..{2,3})?\//;

		if (sayso.location.href.match('bing.com/search'))
		{
			searchType = 1; // bing (these ids match lookup_search_engines table)
			searchRegex = /q=([^&]+)&/g;
		}
		else if (googleEngineRegexp.test(sayso.location.href))
		{
			searchType = 2; // google
			searchRegex = /(?:\?|&)q=([^&]+)/;
		}
		else if (sayso.location.href.match('search.yahoo.com'))
		{
			searchType = 3; // yahoo
			searchRegex = /[\?&]?p=([^&]+)&/g;
		}

		if (searchType)
		{

			var url = '//' + sayso.baseDomain + '/api/metrics/search-engine-submit';
			var data = {
				type_id : searchType
			};

			var searchQueryArray = searchRegex.exec(sayso.location.href);
			if (searchQueryArray != null && searchQueryArray.length > 1)
			{
				var searchQuery = searchQueryArray[1];
				if (searchQuery != null && searchQuery != "") {
					data['query'] = searchQuery;
					behaviorTracker.search(url, data);
				}
			}
			else
			{
				warn('On search page, but no query found');
			}

			// We are in Google, let's monitor the query field
			if(searchType == 2)
			{
				// remeber initial value in search field
				var lastQueryValue  = $SQ('input[name=q]').val();
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
				$SQ.doTimeout(checkInterval, function()
				{
					// any changes
					var currentQueryValue = $SQ('input[name=q]').val();

					// yes, reset all
					if(currentQueryValue != lastQueryValue)
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
							if(!statsSent && currentQueryValue != null && currentQueryValue != "")
							{
								// send now asynchronously...
								data['query'] = currentQueryValue;
								behaviorTracker.search(url, data);
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

	// popup/x-domain Tweet tracking
	if (sayso.location.href.match('twitter.com/intent')) {

		var tweetUrl = decodeURIComponent(/(?:\?|&)url=([^&]+)/.exec(sayso.location.search)[1]);
		var tweet = $SQ('#status').val();
		$SQ('#status').keyup(function () {
			// on every key event we capture the full contents. ensures that
			// the tweet isn't removed by Twitter before we grab it
			tweet = $SQ(this).val();
		});
		// use mousedown, not click. click is not reliable because the window
		// is closed very quickly after submitting the tweet which kills the ajax call
		$SQ('#update-form input.submit').mousedown(function () {
			behaviorTracker.socialActivity(tweetUrl, tweet, 2);
			$SQ(this).unbind('mousedown');
		});
	// Tweet tracking on Twitter.com
	} else if (sayso.location.hostname.match('twitter.com') && $SQ('div.tweet-box textarea').length) {

		var tweet = '';

		// append to what is already bound...
		$SQ('div.tweet-box textarea').bind('keyup', function()
		{
			// since there is a race condition between
			// when our click event is fired and Twitter removes
			// the content of the tweet box, then we just
			// continuously capture the contents here
			tweet = $SQ(this).val();
		});

		// append to what is already bound...
		$SQ('div.tweet-box div.tweet-button-sub-container').bind('click', function(e)
		{
			try
			{
				behaviorTracker.socialActivity(sayso.location.href, tweet, 2);
				e.preventDefault();
				tweet = '';
			}
			catch(ex)
			{
				warn('Exception: '+ ex.getMessage());
			}
		});
	}

	// ================================================================
	// Facebook Like

	if (inIframe) {
		if (sayso.location.href.match('facebook.com/sharer/sharer')) {

			var textArea = $SQ('textarea').first(),
				comment = textArea.val(),
				url = decodeURIComponent(/(?:\?|&)u=([^&]+)/.exec(sayso.location.search)[1]);

			textarea.keyup(function () {
				comment = $SQ(this).val();
			});

			$SQ('input[name=share]').click(function () {
				if (comment.length && comment !== 'Write Something...') {
					behaviorTracker.socialActivity(url, comment, 1);
				}
			});
		} else if (sayso.location.href.match('facebook.com/plugins/comment_widget_shell')) {
			sayso.log('In comment widget!!'); // can't get this working, iframe loads into a hidden div ?
			var comment = $SQ('textarea.connect_comment_widget_full_input_textarea').val();
			sayso.log(comment);
		} else { // location.href.match('facebook.com/plugins|facebook.com/widgets')
			$SQ('a.connect_widget_like_button').unbind('click').click(function () {
				var likedUrl = decodeURIComponent(/href=([^&]*)/g.exec(sayso.location.search)[1]);
				behaviorTracker.socialActivity(likedUrl, '', 1)
			});
		}
	}

	// ================================================================
	// ADjuster

//	if (!sayso.flags.match('adjuster_ads')) return; // globally disabled ad detection/replacement

	log('ADjuster ad handling enabled');

	var adTargets = sayso.state.adTargets;
	if (!adTargets) adTargets = {};

	var studyAdClicks = [];

	if (!inIframe) {
		// ADjuster Click-Thrus ------------------------

		log('Ad Targets: ', adTargets);
		// { creative12 : { urlSegment : 'foo/diamonds', type : 'creative', type_id : 12 }, campaign234 : { etc
		for (var key in adTargets) {
			var viewedStudyAd = adTargets[key];
			if (sayso.location.href.indexOf(viewedStudyAd.ad_target) > -1) {
				studyAdClicks.push(viewedStudyAd.id);
			}
		}

		if (studyAdClicks.length > 0) {
			// click thrus!
			ajax({
				url : '//' + sayso.baseDomain + '/api/metrics/track-study-ad-clicks',
				data : {
					url : sayso.location.href,
					study_ad_clicks : $SQ.JSON.stringify(studyAdClicks)
				},
				success : function (response) {
					log('ADjuster: Click Through (' + adTarget.type + '/' + adTarget.typeId + ')');
					forge.message.broadcastBackground('delete-ad-targets', studyAdClicks);
				}
			});
		}
	}

	// ADjuster Setup Studies ------------------------

	var studyAdViews = [];
	var adsFound = 0;
	var replacements = 0;

	// non-existent OR expired studies
	forge.message.broadcastBackground('get-studies', {},
		function (studyAds) {

			var studyAd = null;

			if (!inIframe) log('Current Study Ads: ', studyAds);

			// study ads
			for (var a in studyAds.items) {
				studyAd = studyAds.items[a];
				if (studyAd && studyAd.existing_ad_tag && (inIframe ? sayso.parentLocation : sayso.location).host.match(studyAd.existing_ad_domain))
					processStudyAd(studyAd);
			} // study ads

			studyAdsProcessingComplete();
		}
	);


	/**
	 * Process each tag including ad detection and/or replacement
	 * - running this method assumes a domain matches for the current URL
	 * - this function inherits currentActivity for the current cell
	 */
	function processStudyAd (studyAd) {
		log('studyAd.existing_ad_tag: ');
		log(studyAd.existing_ad_tag);

		var jTag = false;
		var jTagContainer = false;

		if (studyAd.existing_ad_type == "image") {
			jTag = $SQ('img[src*="' + studyAd.existing_ad_tag + '"]');
		} else if (studyAd.existing_ad_type == "flash") {
			jTag = $SQ('embed[src*="' + studyAd.existing_ad_tag + '"]');
			if (!jTag || !jTag.length) {
				// Try to search (using jQuery) for the param element (though on IE and possibly other browsers, params are not in the DOM).
				jTag = $SQ('param[name="movie"][value*="'+studyAd.existing_ad_tag+'"]');

				// If flash is still not found on this page, try looking inside all the <object> tags' children (i.e. the params)
				if (!jTag || !jTag.length) {
					$SQ('object param[name="movie"]').each(function() {
						if ($SQ(this).attr('value').indexOf(studyAd.existing_ad_tag) > -1) {
							jTag = $SQ(this);
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
		} else if (studyAd.existing_ad_type == "facebook") {
			jTag = $SQ('div[id*="' + studyAd.existing_ad_tag + '-id_"]');
		}


		if (!jTag || !jTag.length) {
			log('No Matches');
			return;
		}

		log('Match', jTag);

		if (!jTagContainer) jTagContainer = jTag.parent();

		if (jTagContainer.is('object')) {
			// If we found a param tag inside an <object> tag, we want the parent of *that*
			jTagContainer = jTagContainer.parent();
		}

		jTagContainer.css('position', 'relative');

		// tag exists
		adsFound++;
		var adTarget = null,
			adTargetId = ''; // used as a JS optimization for searching the adTargets object

		if (studyAd.type == "creative") { // ADjuster Creative ------------

			replacements++;

			// replace ad
			adWidth = jTag.innerWidth();
			adHeight = jTag.innerHeight();
			var newTag = $SQ(document.createElement('div'));
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
						<div class="fbEmu fbEmuEgo"> \
							<a class="fbEmuTitleBodyImageLink emuEvent1  fbEmuLink" href="'+studyAd.ad_target+'" target="_blank"> \
								<div class="fbEmuTitleBodyImageDiv"> \
									<div class="title"><span class="forceLTR">'+studyAd.replacement_ad_title+'</span></div> \
									<div class="clearfix uiImageBlock image_body_block"> \
										<div class="image fbEmuImage uiImageBlockImage uiImageBlockMediumImage lfloat"> \
											<img class="img" src="'+studyAd.replacement_ad_url+'" alt=""> \
										</div> \
										<div class="uiImageBlockContent "> \
											<div class="body"><div class="forceLTR">'+studyAd.replacement_ad_description+'</div></div> \
										</div> \
									</div> \
								</div> \
							</a> \
							<div class="inline"><div class="action"><span class="fbEmuContext">&nbsp;</span></div></div> \
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

			log('ADjuster: Creative Replacement');

		} else { // ADjuster Campaign ------------------------

			// record view of the campaign
			studyAdViews.push(studyAd.id);

			log('ADjuster: Campaign View');

		}

		// update list of ad targets so we can later verify click throughs
		// see Page View section above where this is checked

		forge.message.broadcastBackground('add-ad-target', studyAd);
	}

	// Track ad views!

	// load timer is used so that asynchronous JS does not run this Ajax call too early
	// and to prevent the need for deeply nested callbacks

	function studyAdsProcessingComplete() {
		new $SQ.jsLoadTimer().setMaxCount(50).start(
			function () { return adsFound; }, // if
			function () {										 // then
				log('Ads matched ' + adsFound + '. Ads replaced ' + replacements);
				ajax({
					url : '//' + sayso.baseDomain + '/api/metrics/track-study-ad-views',
					data : {
						// note: user_id, starbar_id are included in ajax() wrapper
						// study_id is associated via cell id, which is included in cellAdActivity
						url : (inIframe ? sayso.parentLocation : sayso.location).href,
						study_ad_views : $SQ.JSON.stringify(studyAdViews)
					},
					success : function (response) {

					}
				});
			},
			function () { // else
				log('No ads match');
			}
		);
	}

	adminFunctions(); // not sure how to approach this just yet. Probably need to pass a user role id (e.g. admin+) in the request, and check that first.

	function adminFunctions () {
		// Detect and log flash files on this page to assist admin find tags
		var tempLink = document.createElement('a');
		lastIndex = -1;

		$SQ('embed').each(function(index) {
			var embedElem = $SQ(this);
			var filename = embedElem.attr('src');
			tempLink.href = filename;
			filename = tempLink.pathname.split('/').pop(); // filename only, e.g. blah.swf
			if (embedElem.parent().is('object')) {
				embedElem = embedElem.parent(); // just for logging purposes, since chrome will only highlight the embed if it is NOT contained in an <object>
			}
			log('Tag '+(index+1)+' (copy and paste this): "'+filename+'"\nElement '+(index+1)+' (roll over this to visually confirm): ', embedElem);
			lastIndex = index;
		});

		lastIndex++;

		$SQ('object').each(function(index) {
			var objectElem = $SQ(this);
			if (objectElem.children('embed').length > 0) { // already found in previous loop
				return true;
			}

			paramTags = objectElem.children();
			for (i = 0; i < paramTags.length; i++) {
				if (paramTags.eq(i).attr('name') && paramTags.eq(i).attr('name').toLowerCase() == "movie") { // We are only interested in the "movie" param (i.e. the URL of the movie)
					var filename = paramTags.eq(i).attr('value');
					tempLink.href = filename;
					filename = tempLink.pathname.split('/').pop(); // filename only, e.g. blah.swf
					log('Tag '+lastIndex+' (copy and paste this): "'+filename+'"\nElement '+lastIndex+' (roll over this to visually confirm): ', objectElem);
					lastIndex++;

					// Go to next object tag
					return true;
				}
			}
		});

		log('Detected '+lastIndex+' flash file(s) ' + (inIframe ? 'in this iframe' : 'on this page'));

	}
});
