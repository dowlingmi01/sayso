/*
 * jQuery doTimeout: Like setTimeout, but better! - v1.0 - 3/3/2010
 * http://benalman.com/projects/jquery-dotimeout-plugin/
 *
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */
(function($){var a={},c="doTimeout",d=Array.prototype.slice;$[c]=function(){return b.apply(window,[0].concat(d.call(arguments)))};$.fn[c]=function(){var f=d.call(arguments),e=b.apply(this,[c+f[0]].concat(f));return typeof f[0]==="number"||typeof f[1]==="number"?this:e};function b(l){var m=this,h,k={},g=l?$.fn:$,n=arguments,i=4,f=n[1],j=n[2],p=n[3];if(typeof f!=="string"){i--;f=l=0;j=n[1];p=n[2]}if(l){h=m.eq(0);h.data(l,k=h.data(l)||{})}else{if(f){k=a[f]||(a[f]={})}}k.id&&clearTimeout(k.id);delete k.id;function e(){if(l){h.removeData(l)}else{if(f){delete a[f]}}}function o(){k.id=setTimeout(function(){k.fn()},j)}if(p){k.fn=function(q){if(typeof p==="string"){p=g[p]}p.apply(m,d.call(n,i))===true&&!q?o():e()};o()}else{if(k.fn){j===undefined?e():k.fn(j===false);return true}else{e()}}}})($SQ);


$SQ(function () {

    // setup
    
    // PENDING: how does this factor in to iframes. Does the sayso object get transferred correctly between the iframe's window?? if not, how to handle that??
    // what if anything needs to be communicated to the 'top' window??

    if (!window.sayso.study) window.sayso.study = {};
    
    var sayso = window.sayso,
        starbar = window.sayso.starbar,
        log = window.sayso.log,
        warn = window.sayso.warn;

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
    
    // get Study data
    
    ajax({
        url : 'http://' + sayso.baseDomain + '/api/study/get-all',
        data : {
            page_number : 1,
            page_size : 10
        },
        success : function (response) {
            sayso.study.studies = response.data;
        } 
    });

    
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

    // NOTE: these are currently *always* firing. In the future these should fire only
    // if the current study is set to include them. @todo add conditional logic for that

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


	// Ad Creative Stuff
	// Note 1: See Bottom of this file for ajax() call that calls processStudyCollection
	// Note 2: Intentionally using for() instead of each() for performance reasons (avoid instantiating functions in loops!)
	// Note 3: JS passes arrays to functions by reference so this should be both easy to read and efficient...

	function processStudyCollection (studyCollection) {
		var result = {
			type : 'Study_Collection_Result',
			study_collection_id : studyCollection.id,
			user_id : starbar.user.id,
			study_results : []
		}
		if (studyCollection.type === 'Study_Collection' 
			&& studyCollection.count 
			&& studyCollection.total_items_found 
			&& studyCollection.items
			&& studyCollection.items.length
		) {
			for (i=0; i < studyCollection.items.length; i++) {
				study = studyCollection.items[i];
				result.study_results.push(processStudy(study));
			}
		}
		
		log('Study_Collection_Result', result);

		ajax({
			url : 'http://' + sayso.baseDomain + '/api/metrics/submit-study-collection-result',
			data : {
				study_collection_result : result
			},
			success : function (response) {}
		});
	}

	function processStudy (study) {
		var result = {
			type : 'Study_Result',
			study_id : study.id,
			study_cell_results : []
		}
		if (study.type === 'Study'
			&& study._cells
			&& study._cells.type === 'Study_Cell_Collection'
			&& study._cells.count
			&& study._cells.total_items_found
			&& study._cells.items
			&& study._cells.items.length
		) {
			for (i=0; i < study._cells.items.length; i++) {
				studyCell = study._cells.items[i];
				result.study_cell_results.push(processStudyCell(studyCell));
			}
		}
		return result;
	}

	function processStudyCell (studyCell) {
		var result = {
			type : 'Study_Cell_Result',
			study_cell_id : studyCell.id,
			study_tag_results : []
		}
		if (studyCell.type === 'Study_Cell'
			&& studyCell._tags
			&& studyCell._tags.type === 'Study_Tag_Collection'
			&& studyCell._tags.count
			&& studyCell._tags.total_items_found
			&& studyCell._tags.items
			&& studyCell._tags.items.length
		) {
			for (i=0; i < studyCell._tags.items.length; i++) {
				tag = studyCell._tags.items[i];
				result.study_tag_results.push(processStudyTag(tag));
			}
		}
		return result;
	}

	function processStudyTag (tag) {
		var result = {
			type : 'Study_Tag_Result',
			tag_id : tag.id,
			number_of_occurances : 0
		};
		if (tag.type === 'Study_Tag'
			&& tag._creatives
			&& tag._creatives.type === 'Study_Creative_Collection'
			&& tag._creatives.count
			&& tag._creatives.total_items_found
			&& tag._creatives.items
			&& tag._creatives.items.length
		) {
			for (i=0; i < tag._creatives.items.length; i++) {
				creative = tag._creatives.items[i];
				result.number_of_occurances += processStudyCreative(creative);
			}
		}
		return result;
	}

	function processStudyCreative (creative) {
		var numberOfOccurrences = 0;
		if (creative.type === 'Study_Creative'
			&& creative.url
			&& creative.target_url
			&& creative.selector
		) {
			adElems = $SQ(creative.selector);
			for (i=0; i < adElems.length; i++) {
				adElem = adElems.eq(i);
				adElemContainer = adElem.parent();
				adElemContainer.html('<a href="'+creative.target_url+'" target="_new"><img src="'+creative.url+'" border=0 /></a>');
			}
			numberOfOccurrences = adElems.length;
		}
		return numberOfOccurrences;
	}
	
	ajax({
		url : 'http://' + sayso.baseDomain + '/api/study/get-all',
		success : function (response) {
			var studyCollection = response.data;
			log('Study_Collection Received', studyCollection);
			//processStudyCollection (studyCollection);
		}
	});

});













