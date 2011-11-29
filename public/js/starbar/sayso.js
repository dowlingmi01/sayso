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

    var sayso = window.sayso,
        starbar = window.sayso.starbar;
    
    // Check required params
    
    if (!starbar.id || !starbar.user.id || !starbar.user.key || !starbar.authKey) return;
    
    // ADjuster blacklist
    
    var trackerBlackList = [/(sayso|saysollc)\.com/, /say\.so/];
    for (var i = 0, ln = trackerBlackList.length; i < ln; i++)
    {
        if (trackerBlackList[i].test(location.href))
        {
            return;
        }
    }
    
    // setup
    
    var log = window.sayso.log,
        warn = window.sayso.warn,
        inIframe = (top !== self);

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

    if (inIframe) log('iFrame');
    
    /**
     * Helper function which can be used outside this context
     *
     * @example window.sayso.behaviors.socialActivity('foo.com', 'blah blah', 1)
     */
    window.sayso.behaviors = new function () {

        var _instance = this;

        this.pageView = function () {
            ajax({
                url : '//' + sayso.baseDomain + '/api/metrics/page-view-submit',
                data : {
                    url : encodeURIComponent(location.href)
                },
                success : function (response) {}
            });
        };
        
        this.search = function (url, data) {
            ajax({
                url     : url,
                data    : data,
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
        sayso.behaviors.pageView();
    }

    // ================================================================
    // Search

    if (!inIframe) {
        
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
    
        if (searchType)
        {
    
            var url = '//' + sayso.baseDomain + '/api/metrics/search-engine-submit';
            var data = {
                type_id : searchType
            };
    
            var searchQueryArray = searchRegex.exec(location.href);
            if (searchQueryArray != null && searchQueryArray.length > 1)
            {
                var searchQuery = searchQueryArray[1];
                data['query'] = searchQuery;
                sayso.behaviors.search(url, data);
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
                                sayso.behaviors.search(url, data);
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
    // @todo handle iframe'd tweets
    // twitter.com/intent/tweet .. watch #update-form submit
    
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
                window.sayso.behaviors.socialActivity(window.location.href, tweet, 2);
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
    // @todo handle iframe'd likes, shares, reccomends, etc
    // watch when current page (iframe) is one of the following URLs
    // then: watch for .connect_widget_like_button click (for example)
    // @hack the following JS uses mouseover/mouseout to predict clicking 
    //       Like until we get the iframe'd version done

    var likeButtons = $SQ('iframe[src*="facebook.com/plugins/like.php"],iframe[src*="facebook.com/widgets/like.php"]');

    if (likeButtons.length) {
        var liked = false;
        likeButtons.bind('mouseover', function (eventOver) {

            // only register 1 Like event per page
            if (liked) return;

            var timerRunning = true;

            // register Like if mouse stays over for enough time
            // to aim, click and get feedback (button changes)
            var mouseOutTimer = setTimeout(function () {
                timerRunning= false;
                liked = true;
                sayso.behaviors.socialActivity(location.href, '', 1);
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
    // ADjuster 
    
    if (!sayso.flags.match('adjuster_ads')) return; // globally disabled ad detection/replacement
    log('ADjuster ad handling enabled');
    
    // ADjuster Click-Thrus ------------------------
    
    var adTargets = JSON.parse(sayso.study.adTargets);
    
    if (!inIframe) log('Ad Targets: ', adTargets);
    // { creative12 : { urlSegment : 'foo/diamonds', type : 'creative', type_id : 12 }, campaign234 : { etc
    for (var key in adTargets) {
        var adTarget = adTargets[key];
        if (location.href.match(adTarget.urlSegment)) {
            // click thru!
            ajax({
                url : '//' + sayso.baseDomain + '/api/metrics/track-click-thru',
                data : {
                    url : location.href,
                    url_segment : adTarget.urlSegment,
                    type : adTarget.type,
                    type_id : adTarget.typeId
                },
                success : function (response) {
                    log('ADjuster: Click Through (' + adTarget.type + '/' + adTarget.typeId + ')');
                }
            });
            break;
        }
    }

    // ADjuster Setup Studies ------------------------

    if (sayso.study.studies && 
        ((new Date().getTime()) - sayso.study.studiesTimestamp) < (60 * 10 /* minutes */ * 1000)) { // study data is cached
        
        if (sayso.study.studies && sayso.study.studies !== 'none') {
            // valid studies 
            processStudyCells(JSON.parse(sayso.study.studies));
        } // else no studies for current user, wait till cache time expires to check again
        
    } else {
        
        // non-existent OR expired studies
        ajax({
            url : '//' + sayso.baseDomain + '/api/study/get-all',
            data : {
                page_number : 1,
                page_size : 5
            },
            success : function (response) {
                
                if (response.status === 'error') {
                    sayso.warn(response.data);
                    return;
                }
                
                if (!response.data.items || !response.data.items.length) {
                    // no studies for this user
                    var app = KOBJ.get_application(sayso.starbar.kynetxAppId);
                    app.raise_event(
                        'update_studies', 
                        { 
                            'studies' : 'none',
                            'studies_timestamp' : new Date().getTime()
                        }
                    );
                    return;
                }
                
                // Simplify server studies into something manageable (and storable)
                
                /* Example study cells array
                
                cells = [
                     {
                         id : 1, 
                         tags : [
                             {
                                 id : 1, 
                                 tag : 'embed*=blah', 
                                 target_url : 'buystuff.com', 
                                 domains : [
                                     'foo.com', 
                                     'bar.com'
                                 ], 
                                 creatives : [
                                     {
                                         id : 1,
                                         url : 'http/to/the/creative',
                                         target_url : 'buymorestuff.com'
                                     }
                                 ]
                             }
                         ]
                     }
                 ];
                 */
                
                var serverStudies = response.data,
                    numServerStudies = serverStudies.items.length,
                    cells = [];

                // studies
                for (var s = 0; s < numServerStudies; s++) {
                    var study = serverStudies.items[s];

                    numCells = study._cells.items.length;
                    if (!numCells) continue;

                    // cells
                    for (var c = 0; c < numCells; c++) {
                        var cell = study._cells.items[c];

                        var numTags = cell._tags.items.length;
                        if (!numTags) continue;
                        
                        var cellIndex = cells.push({
                            id : cell.id, 
                            tags : []
                        });
                        cellIndex--;
                        
                        // tags
                        for (var t = 0; t < numTags; t++) {
                            var tag = cell._tags.items[t];

                            var tagIndex = cells[cellIndex].tags.push({
                                id : tag.id,
                                tag : tag.tag,
                                target_url : tag.target_url,
                                domains : '',
                                creatives : []
                            });
                            tagIndex--;
                            
                            // domains 
                            var numDomains = tag._domains.items.length;
                            for (var d = 0; d < numDomains; d++) {
                                var domain = tag._domains.items[d];
                                if (d > 0) cells[cellIndex].tags[tagIndex].domains += '|';
                                cells[cellIndex].tags[tagIndex].domains += domain.domain;
                            } // domains 
                            
                            var numCreatives = tag._creatives.items.length;
                            for (var cr = 0; cr < numCreatives; cr++) {
                                var creative = tag._creatives.items[cr];
                                cells[cellIndex].tags[tagIndex].creatives.push({
                                    id : creative.id,
                                    url : creative.url,
                                    target_url : creative.target_url
                                });
                            } // creatives
                        } // tags
                    } // cells
                } // studies
                
                // process cells and update the cache
                
                setTimeout(function () {
                    
                    processStudyCells(cells);
                    
                    var app = KOBJ.get_application(sayso.starbar.kynetxAppId);
                    app.raise_event(
                        'update_studies', 
                        { 
                            'studies' : JSON.stringify(cells),
                            'studies_timestamp' : new Date().getTime()
                        }
                    );
                }, 2000);
                    
            }
        });
    }    
    
    /**
     * Process all study cells
     */
    function processStudyCells (cells) {
        
        var cellAdActivity = {}, // { id : 1, tagViews : [], creativeViews : []}
            currentActivity = null,
            adsFound = 0,
            replacements = 0;
        
        var numCells = cells.length;
        for (var c = 0; c < numCells; c++) {
            var cell = cells[c],
                tags = cell.tags;
            
            // setup the object that will be sent back
            // to the server, where cell id is the top
            // level key for each group of tag/creative views
            currentActivity = cellAdActivity[cell.id] = {
                tagViews : [],
                creativeViews : []
            };
            
            var numTags = tags.length;
            for (var t = 0; t < numTags; t++) {
                var tag = tags[t];
                if (inIframe) {
                    // @hack, for iframes currently firing ad detection on all domains
                    // @todo figure out how to pass "top" location data into child iframes
                    // so we can check to make sure the iframes parent matches before firing
                    processTag (tag);
                } else {
                    if (location.host.match(tag.domains)) processTag(tag);
                }
            }
        }
        
        /**
         * Process each tag including ad detection and/or replacement
         * - running this method assumes a domain matches for the current URL
         * - this function inherits currentActivity for the current cell
         */
        function processTag (tag) {
            
            var jTag = $SQ(tag.tag);
            
            if (!jTag.length) return;

            log('Match', jTag);
            
            // tag exists
            jTagContainer = jTag.parent();
            if (jTag.is('embed') && jTagContainer.is('object')) {
                // If we found an embed tag inside an <object> tag, we want the parent of *that*
                jTagContainer = jTagContainer.parent();
            }

            jTagContainer.css('position', 'relative');

            adsFound++;
            var adTarget = null,
                adTargetId = ''; // used as a JS optimization for searching the adTargets object
            
            var numCreatives = tag.creatives.length;
            if (numCreatives) { // ADjuster Creative ------------

                replacements++;

                // @hack just grab the first creative for now
                // @todo enable cycling through each creative
                var creative = tag.creatives[0];

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
                
                log('ADjuster: Creative Replacement');

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
                
                log('ADjuster: Campaign View');
                
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
                        url : '//' + sayso.baseDomain + '/api/study/get-all',
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
        
        // Track ad views!
        
        // load timer is used so that asynchronous JS does not run this Ajax call too early
        // and to prevent the need for deeply nested callbacks

        new $SQ.jsLoadTimer().setMaxCount(50).start(
            function () { return numStudies === s && adsFound; }, // if
            function () {                                         // then
                log('Ads matched ' + adsFound + '. Ads replaced ' + replacements);
                ajax({
                    url : '//' + sayso.baseDomain + '/api/metrics/track-ad-views',
                    data : {
                        // note: user_id, starbar_id are included in ajax() wrapper
                        // study_id is associated via cell id, which is included in cellAdActivity
                        cell_activity : JSON.stringify(cellAdActivity)
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
        log('Detected '+$SQ('embed').length+' flash file(s) ' + (inIframe ? 'in this iframe' : 'on this page'));
        $SQ('embed').each(function(index) {
            var embedElem = $SQ(this);
            var filename = embedElem.attr('src').match(/[^/]+$/)[0];
            if (embedElem.parent().is('object')) {
                embedElem = embedElem.parent(); // just for logging purposes, since chrome will only highlight the embed if it is NOT contained in an <object>
            }
            log('Selector '+(index+1)+' (copy and paste this): embed[src*="'+filename+'"]\nElement '+(index+1)+' (roll over this to visually confirm): ', embedElem);
        });
    }
});




