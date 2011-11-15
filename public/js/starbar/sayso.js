/*
 * jQuery doTimeout: Like setTimeout, but better! - v1.0 - 3/3/2010
 * http://benalman.com/projects/jquery-dotimeout-plugin/
 *
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */
(function($){var a={},c="doTimeout",d=Array.prototype.slice;$[c]=function(){return b.apply(window,[0].concat(d.call(arguments)))};$.fn[c]=function(){var f=d.call(arguments),e=b.apply(this,[c+f[0]].concat(f));return typeof f[0]==="number"||typeof f[1]==="number"?this:e};function b(l){var m=this,h,k={},g=l?$.fn:$,n=arguments,i=4,f=n[1],j=n[2],p=n[3];if(typeof f!=="string"){i--;f=l=0;j=n[1];p=n[2]}if(l){h=m.eq(0);h.data(l,k=h.data(l)||{})}else{if(f){k=a[f]||(a[f]={})}}k.id&&clearTimeout(k.id);delete k.id;function e(){if(l){h.removeData(l)}else{if(f){delete a[f]}}}function o(){k.id=setTimeout(function(){k.fn()},j)}if(p){k.fn=function(q){if(typeof p==="string"){p=g[p]}p.apply(m,d.call(n,i))===true&&!q?o():e()};o()}else{if(k.fn){j===undefined?e():k.fn(j===false);return true}else{e()}}}})($SQ);

// available to this script:
//     $SQ.jsLoadTimer
//     $SQ.cssLoadTimer
// (see starbar-loader.js)

$SQ(function () {

    if (top !== self) return; // <-- hack to prevent iframe processing until we are ready for this (pending NY demos)
    // NOTE: implement caching (per @todo below) before implementing iframe support

    // setup

    var sayso = window.sayso,
        starbar = window.sayso.starbar,
        log = window.sayso.log,
        warn = window.sayso.warn;

    if (!sayso.study) sayso.study = {};

    var ajax = function (options) {
        options.data = $SQ.extend(options.data || {}, {
            auth_key : starbar.authKey,
            user_id : starbar.user.id,
            user_key : starbar.user.key,
            starbar_id : starbar.id,
            renderer : 'jsonp'
        });
        options.dataType = 'jsonp';
        return $SQ.ajax(options);
    };

    /**
     * Helper function which can be used outside this context (e.g. in KRL)
     *
     * @example window.sayso.helper.socialActivity('foo.com', 'blah blah', 1)
     */
    window.sayso.helper = new function () {

        var _instance = this;

        // social activity

        // see KRL for Facebook Like logic

        this.socialActivity = function (url, content, type_id) {
            ajax({
                url : 'http://' + sayso.baseDomain + '/api/metrics/social-activity-submit',
                data : {
                    type_id : type_id,
                    url : url,
                    content : content
                },
                success : function (response) {
                    log('Social activity', response);
                }
            });
        };
    };

    // Blacklist sayso domains before any tracking
    //sayso.log('debug >>> ' + location.href);
    var trackerBlackList = [/(sayso|saysollc)\.com/, /say\.so/];
    for (var i = 0, ln = trackerBlackList.length; i < ln; i++)
    {
        if (trackerBlackList[i].test(location.href))
        {
            warn('Disabling tracking on own domains...');
            return;
        }
    }
    // ... end blacklist...

    // Behavioral tracking

    // NOTE: basic behavioral tracking (i.e. not ad tracking) fires
    // continuously regardless of study behavioral settings (filtering will
    // done later based on study settings)

    // ================================================================
    // Page View

    ajax({
        url : 'http://' + sayso.baseDomain + '/api/metrics/page-view-submit',
        data : {
            url : encodeURIComponent(location.href)
        },
        success : function (response) {
            log('Track page view', response);
        }
    });

    // ================================================================
    // Search

    var searchType = 0,
        searchRegex = '';

    var googleEngineRegexp = /google(\..{2,3})+(\..{2,3})?\//;

    if (location.href.match('bing.com/search'))
    {
        searchType = 1; // bing (these ids match lookup_search_engines table)
        searchRegex = /q=([^&]+)&/g;
    }
    else if (googleEngineRegexp.test(location.href))
    {
        searchType = 2; // google
        searchRegex = /&q=([^&]+)&?.*$/g;
    }
    else if (location.href.match('search.yahoo.com'))
    {
        searchType = 3; // yahoo
        searchRegex = /[\?&]?p=([^&]+)&/g;
    }

    function onSearchEvent(url, data)
    {
        ajax({
            url     : url,
            data    : data,
            success : function (response) {
                log('Search', response);
            }
        });
    }

    if (searchType)
    {

        var url = 'http://' + sayso.baseDomain + '/api/metrics/search-engine-submit';
        var data = {
            type_id : searchType
        };

        var searchQueryArray = searchRegex.exec(location.href);
        if (searchQueryArray != null && searchQueryArray.length > 1)
        {
            var searchQuery = searchQueryArray[1];
            data['query'] = searchQuery;
            onSearchEvent(url, data);
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
            var statsSent       = true;
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
                    statsSent       = false;
                }
                else
                {
                    // increment the start and check
                    startInterval += checkInterval;
                    if(startInterval >= validInterval)
                    {
                        // no stats sent? send it!
                        if(!statsSent)
                        {
                            // send now asynchronously...
                            data['query'] = currentQueryValue;
                            onSearchEvent(url, data);
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

    // ================================================================
    // Tweets

    if (location.hostname.match('twitter.com'))
    {

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
                window.sayso.helper.socialActivity(window.location.href, tweet, 2);
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

    var likeButtons = $SQ('iframe[src*="facebook.com/plugins/like.php"],iframe[src*="facebook.com/widgets/like.php"]');

    if (likeButtons.length) {
        var liked = false;
        likeButtons.bind('mouseover', function (eventOver) {
            //sayso.log('Over Like');

            // only register 1 Like event per page
            if (liked) return;

            var timerRunning = true;

            // register Like if mouse stays over for enough time
            // to aim, click and get feedback (button changes)
            var mouseOutTimer = setTimeout(function () {
                timerRunning= false;
                liked = true;
                //sayso.log('Like!');
                sayso.helper.socialActivity(location.href, '', 1);
            }, 700); // aim + click + feedback

            // cancel Like if mouse passes back out quickly
            $SQ(this).unbind('mouseout').bind('mouseout', function (eventOut) {
                if (timerRunning) {
                    timerRunning = false;
                    clearTimeout(mouseOutTimer);
                }
            });
        });
    }

    // ================================================================
    // ADjuster Ad-Tracking/Replacement/Click-Thrus

    // @todo optimize the retreival of active studies for the current user.
    // this will likely include a combination of a server-side daemon for handling
    // study cell assignments, server-side caching (which the daemon will also
    // manage the state of depending on assignments) and client-side caching.
    // For now, we just retreive all studies on every page load (though not every iframe)

    ajax({
        url : 'http://' + sayso.baseDomain + '/api/study/get-all',
        data : {
            page_number : 1,
            page_size : 10
        },
        success : function (response) {
            studies = response.data;
            log(studies);
            if (studies.items.length) {
                processStudyCollection(studies);
            }
        }
    });

    // ADjuster Click-Thrus ------------------------
    
    var adTargets = JSON.parse(sayso.study.adTargets);
    log(adTargets);
    for (var key in adTargets) {
        var adTarget = adTargets[key];
        if (location.href.match(adTarget.urlSegment)) {
            // click thru!
            ajax({
                url : 'http://' + sayso.baseDomain + '/api/metrics/track-click-thru',
                data : {
                    url_segment : adTarget.urlSegment,
                    type : adTarget.type,
                    type_id : adTarget.typeId
                },
                success : function (response) {
                    log('Tracked click thru for ' + adTarget.type + ' (' + adTarget.typeId + ')');
                }
            });
            break;
        }
    }
    
    /**
     * Process all studies
     */
	function processStudyCollection (studies) {

	    var cellAdActivity = {}, // { id : 1, tagViews : [], creativeViews : []}
	        currentActivity = null,
	        adsFound = 0,
	        replacements = 0,
	        numStudies = studies.items.length;

	    // studies
		for (var s = 0; s < numStudies; s++) {
		    var study = studies.items[s];

		    var numCells = study._cells.items.length;
            if (!numCells) continue;

            // cells
            for (var c = 0; c < numCells; c++) {
                var cell = study._cells.items[c];

                // setup the object that will be sent back
                // to the server, where cell id is the top
                // level key for each group of tag/creative views
                currentActivity = cellAdActivity[cell.id] = {
                    tagViews : [],
                    creativeViews : []
                };

                var numTags = cell._tags.items.length;
                if (!numTags) continue;

                // tags
                for (var t = 0; t < numTags; t++) {
                    var tag = cell._tags.items[t];

                    var numDomains = tag._domains.items.length;

                    // domains (look for valid before processing tag)
                    for (var d = 0; d < numDomains; d++) {
                        var domain = tag._domains.items[d];
                        if (location.host.match(domain.domain)) {
                            processTag(tag);
                            break; // go no further
                        }
                    } // domains
                } // tags
            } // cells
		} // studies

		/**
         * Process each tag including ad detection and/or replacement
         * - running this method assumes a domain matches for the current URL
         * - this function inherits currentActivity for the current cell
         */
        function processTag (tag) {

            var jTag = $SQ(tag.tag);
            if (jTag.length) { // tag exists

                jTagContainer = jTag.parent();
                if (jTag.is('embed') && jTagContainer.is('object')) {
                	// If we found an embed tag inside an <object> tag, we want the parent of *that*
                    jTagContainer = jTagContainer.parent();
				}

                jTagContainer.css('position', 'relative');

                adsFound++;
                var adTarget = null,
                    adTargetId = ''; // used as a JS optimization for searching the adTargets object
                
                var numCreatives = tag._creatives.items.length;
                if (numCreatives) { // ADjuster Creative ------------

                    replacements++;

                    // @hack just grab the first creative for now
                    // @todo enable cycling through each creative
                    var creative = tag._creatives.items[0];

                    // replace ad
                    adWidth = jTagContainer.innerWidth();
                    adHeight = jTagContainer.innerHeight();
					jTag = $SQ(document.createElement('div'));
					jTag.css({
						'width': adWidth+'px',
						'height': adHeight+'px',
						'overflow': 'hidden',
						'display': 'block'
					});
					jTag.html('<a id="sayso-adcreative-'+creative.id+'" href="'+creative.target_url+'" target="_new"><img src="'+creative.url+'" border=0 /></a>');
                    jTagContainer.html('').append(jTag);

                    // record view of the creative
                    currentActivity.creativeViews.push(creative.id);

                    adTarget = {
                        urlSegment : creative.target_url,
                        type : 'creative',
                        typeId : creative.id
                    };
                    adTargetId = 'creative' + creative.id;

                } else { // ADjuster Campaign ------------------------

                    // track ad view
                    currentActivity.tagViews.push(tag.id);

	                // Flash; add wmode transparent, then recreate the flash object (via cloning) to reinsert it into the DOM
                    /*
                    if (jTag.is('embed')) {
                    	jTag.css('z-index', '2000000001');
                    	jTag.attr('wmode', 'transparent');
                    	if (jTag.parent().is('object')) {
                    		oldTag = jTag.parent();
                    		oldTag.prepend('<param name="wmode" value="transparent" />');
                    		newTag = oldTag.clone(true, true);
						} else {
							oldTag = jTag;
							newTag = jTag.clone(true, true);
						}
						oldTag.replaceWith(newTag);
					}
					*/

                    adTarget = {
                        urlSegment : tag.target_url,
                        type : 'campaign',
                        typeId : tag.id
                    };
                    adTargetId = 'campaign' + tag.id;
                    
                }
                
                // update list of ad targets so we can later verify click throughs
                // see Page View section above where this is checked
                
                adTargets[adTargetId] = adTarget;
                
                var app = KOBJ.get_application(sayso.starbar.kynetxAppId);
                app.raise_event(
                    'update_ad_targets', 
                    { 
                        'ad_targets' : JSON.stringify(adTargets)
                    }
                );
                
				/*
				var clickDetectionElem = $SQ(document.createElement('div'));
				clickDetectionElem.css({
					'position': 'absolute',
					'top': 0,
					'right': 0,
					'bottom': 0,
					'left': 0,
					'background': 'none',
					'background-color': 'none',
					'background-image': 'none',
					'display': 'block',
					'z-index': '2000000000',
					'cursor': 'pointer'
				});

				jTagContainer.prepend(clickDetectionElem);
				//clickDetectionElem.css('display', 'block');

				jTagContainer.bind({
					'click': function(e) {
						log('Click detected at X='+e.pageX+', Y='+e.pageY);
						log('Click offset detected at X='+e.offsetX+', Y='+e.offsetY);
						ajax({
							// Replace this with proper ajax call to record the click
							url : 'http://' + sayso.baseDomain + '/api/study/get-all',
							data : {
								page_number : 1,
								page_size : 10
							},
							success : function (response) {
							}
						});
						//clickDetectionElem.css('display', 'none');
					}
				});
				*/
            }
        }

        // Track ad views!

		// load timer is used so that asynchronous JS does not run this Ajax call too early
		// and to prevent the need for deeply nested callbacks

		new $SQ.jsLoadTimer().setMaxCount(50).start(
		    function () { return numStudies === s && adsFound; }, // condition
		    function () {                                         // callback
		        log('Ads found ' + adsFound + '. Replacements ' + replacements);
			    ajax({
                    url : 'http://' + sayso.baseDomain + '/api/metrics/track-ad-views',
                    data : {
                        // note: user_id, starbar_id are included in ajax() wrapper
                        // study_id is associated via cell id, which is included in cellAdActivity
                        cell_activity : JSON.stringify(cellAdActivity)
                    },
                    success : function (response) {

                    }
                });
		    },
		    function () {
		        log('No ads found');
		    }
		);
	}
});




