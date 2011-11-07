
$SQ(function () {

    // setup

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

    // behavioral tracking

    // NOTE: these are currently *always* firing. In the future these should fire only
    // if the current study is set to include them. @todo add conditional logic for that

    // page view

    ajax({
        url : 'http://' + sayso.baseDomain + '/api/metrics/page-view-submit',
        data : {
            url : encodeURIComponent(location.href)
        },
        success : function (response) {
            log('Track page view', response);
        }
    });

    // search

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

    //sayso.log('debug >>> ' + location.href);
    //sayso.log('----- >>> ' + location.href.match('search.yahoo.com'));

    function onSearchEven(url, data)
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

        var searchQueryArray = searchRegex.exec(location.href);
        if (searchQueryArray != null && searchQueryArray.length > 1) {

            var searchQuery = searchQueryArray[1];
            var url = 'http://' + sayso.baseDomain + '/api/metrics/search-engine-submit';
            var data = {
                type_id : searchType,
                query   : searchQuery
            };

            onSearchEven(url, data);

            // We are in google, let's monitor the query field
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
                            // no stats send? send it!
                            if(!statsSent)
                            {
                                // send now asynchronously
                                data['query'] = currentQueryValue;
                                onSearchEven(url, data);
                                // but set check synchronously to avoid repeating...
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
        else 
        {
            warn('On search page, but no query found');
        }
    }

    // Tweets

    if (location.hostname.match('twitter.com')) {

        var tweet = '';
        $SQ('div.tweet-box textarea').keyup(function () {
            // since there is a race condition between
            // when our click event is fired and Twitter removes
            // the content of the tweet box, then we just
            // continuously capture the contents here
            tweet = $SQ(this).val();
        });
        $SQ('div.tweet-box div.tweet-button-sub-container').click(function (e) {
            e.preventDefault();
            sayso.helper.socialActivity(location.href, tweet, 2);
            tweet = '';
        });
    }

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


});













