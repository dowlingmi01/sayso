
$S(function () { 
    
    // setup
    
    var sayso = window.sayso, 
        starbar = window.sayso.starbar,
        log = window.sayso.log,
        warn = window.sayso.warn;
    
    var ajax = function (options) {
        options.data = $S.extend(options.data || {}, {
            auth_key : starbar.authKey,
            user_id : starbar.user.id,
            starbar_id : starbar.id,
            renderer : 'jsonp'
        });
        options.dataType = 'jsonp';
        return $S.ajax(options);
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
    }
    
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
    
    if (location.href.match('bing.com/search')) {
        searchType = 1; // bing (these ids match lookup_search_engines table)
        searchRegex = /q=([^&]+)&/g;
    } else if (location.href.match('google.com/search')) {
        searchType = 2; // google
        searchRegex = /&q=([^&]+)&/g;
    } else if (location.href.match('search.yahoo.com')) {
        searchType = 3; // yahoo
        searchRegex = /&p=([^&]+)&/g;
    }
    
    if (searchType) {
        var searchQueryArray = searchRegex.exec(location.href);
        if (searchQueryArray.length > 1) {
            var searchQuery = searchQueryArray[1]; 
            ajax({
                url : 'http://' + sayso.baseDomain + '/api/metrics/search-engine-submit',
                data : {
                    type_id : searchType,
                    query : searchQuery
                },
                success : function (response) {
                    log('Search', response);
                }
            });
        } else {
            warn('On search page, but no query found');
        }
    }
    
    // Tweets
    
    if (location.hostname.match('twitter.com')) {
        
        var tweet = '';
        $S('div.tweet-box textarea').keyup(function () {
            // since there is a race condition between
            // when our click event is fired and Twitter removes
            // the content of the tweet box, then we just
            // continuously capture the contents here
            tweet = $S(this).val();
        });
        $S('div.tweet-box div.tweet-button-sub-container').click(function (e) {
            e.preventDefault();
            sayso.helper.socialActivity(location.href, tweet, 2);
            tweet = '';
        });
    }
});













