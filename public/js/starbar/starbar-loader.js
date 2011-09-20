
(function () {
    
    var sayso = window.sayso,
        log = window.sayso.log,
        warn = window.sayso.warn;
    
    // bring in the GENERIC CSS
    var css1 = document.createElement('link'); 
    css1.rel = 'stylesheet';
    css1.href = 'http://' + sayso.baseDomain + '/css/starbar-generic.css';
    document.getElementById('sayso-starbar').appendChild(css1);

    // bring in namespaced jQuery $S 
    if (!window.hasOwnProperty('$S')) {
        var js1 = document.createElement('script'); 
        js1.src = 'http://' + sayso.baseDomain + '/js/starbar/jquery-1.6.1.min.js';
        document.getElementById('sayso-starbar').appendChild(js1);
    }
    
    // functions to control load order
    
    function jsLoadTimer () {
        
        var _counter = 0,
            _maxCount = 200, // about 10 seconds (200 x 50 mseconds for each timer)
            _searchSymbol = '',
            _callback = null;
        
        function _waitUntilJsLoaded () {
            if (window.hasOwnProperty(_searchSymbol)) {
                _callback();
                return;
            }
            if (_counter++ > _maxCount) { 
                _callback(); // stop waiting and just fire the callback
                log('Stopped waiting for JS to load (not found: ' + _searchSymbol + ')');
                return;
            }
            setTimeout(_waitUntilJsLoaded, 50);
        }
        
        this.start = function (symbol, callback) {
            _searchSymbol = symbol;
            _callback = callback;
            _waitUntilJsLoaded();
        };
    };
    
    function cssLoadTimer () {
        
        var _counter = 0,
            _maxCount = 200,
            _callback = null;
        
        // create & append a test div to the dom
        var _testCssLoaded = document.createElement("div");
        _testCssLoaded.style = 'display: none !important;';
        // NOTE: appending is req'd with Chrome (FF works w/o appending)
        document.getElementById('sayso-starbar').appendChild(_testCssLoaded);
    
        function _waitUntilCssLoaded () {
            if ($S(_testCssLoaded).css('width') === '1px') {
                $S(_testCssLoaded).css('width', '0px');
                _callback();
                return;
            }
            if (_counter++ > _maxCount) { 
                _callback(); // stop waiting and just fire it
                log('Stopped waiting for CSS to load (not loaded: ' + _testCssLoaded.className + ') ');
                return;
            }
            setTimeout(_waitUntilCssLoaded, 50);
        };
        
        this.start = function (cssClassName, callback) {
            _testCssLoaded.className = cssClassName;
            _callback = callback;
            _waitUntilCssLoaded();
        };
    }
    
    // bring in the Starbar
    var jQueryLoadTimer = new jsLoadTimer();
    jQueryLoadTimer.start('$S', function () {
        log('Loaded - jQuery, generic CSS');
        
        // load auxilliary (namespaced) JS libraries
        var js2 = document.createElement('script'); 
        js2.src = 'http://' + sayso.baseDomain + '/js/starbar/jquery-ui-1.8.16.custom.min.js';
        document.getElementById('sayso-starbar').appendChild(js2);
        
        var js3 = document.createElement('script'); 
        js3.src = 'http://' + sayso.baseDomain + '/js/starbar/jquery.jscrollpane.min.js';
        document.getElementById('sayso-starbar').appendChild(js3);
        
        var js4 = document.createElement('script'); 
        js4.src = 'http://' + sayso.baseDomain + '/js/starbar/jquery.cookie.js';
        document.getElementById('sayso-starbar').appendChild(js4);
        
        var url = 'http://' + sayso.baseDomain;
        
        var params = {
            starbar_id : sayso.starbar.id,
            auth_key : sayso.starbar.authKey,
            user_id : sayso.starbar.user.id,
            user_key : sayso.starbar.user.key
        };
        
        if (sayso.client) { // we must be on the customer's page
            params.client_name = sayso.client.name;
            params.client_uuid = sayso.client.uuid;
            params.client_uuid_type = sayso.client.uuid_type;
        }
        
        $S.ajax({
            dataType: 'jsonp',
            data : params,
            url : 'http://' + sayso.baseDomain + '/starbar/remote',
            success : function (response, status) {
                
                if (response.status === 'error') {
                    // error happened on server (probably in remote/post-install-deliver)
                    // go no further, do not display starbar
                    // situation will likely be rectified by returning to client site
                    // @todo provide feedback to user to return to client site
                    warn(response.data);
                    return;
                }
                
                // load server data for this Starbar
                var starbar = response.data;
                log('Received - ' + starbar.label + ' Starbar');
                log(starbar);
                sayso.starbar.id = starbar.id;
                sayso.starbar.authKey = starbar._auth_key;
                sayso.starbar.user.id = starbar._user.id;
                sayso.starbar.user.key = starbar._user._key;
                
                // update global/persistent vars on kobj.net
                var app = KOBJ.get_application(sayso.starbar.kynetxAppId);
                app.raise_event(
                    'update_global_variables', 
                    { 
                        'starbar_id' : starbar.id, 
                        'auth_key' : starbar._auth_key,
                        'user_id' : starbar._user.id,
                        'user_key' : starbar._user._key
                    }
                );
                
                // load SaySo Javascript (which depends on the above data settings!)
                var js5 = document.createElement('script'); 
                js5.src = 'http://' + sayso.baseDomain + '/js/starbar/sayso.js';
                document.getElementById('sayso-starbar').appendChild(js5);
                
                // load the specific CSS for this Starbar
                var css2 = document.createElement('link'); 
                css2.rel = 'stylesheet';
                css2.href = starbar._css_url;
                document.getElementById('sayso-starbar').appendChild(css2);
                
                // append the HTML to the DOM
                var customCssLoadTimer = new cssLoadTimer(); 
                customCssLoadTimer.start('_sayso_starbar_css_loaded', function () { 
                    log('Loaded - custom CSS');
                    
                    var jQueryLibraryLoadTimer = new jsLoadTimer();
                    jQueryLibraryLoadTimer.start('jQueryUILoaded', function () {
                        log('Loaded - jQuery libs (incl. jQueryUI)');
                        $K('#sayso-starbar').append(starbar._html);
                    });
                });
            }
        });
    });
})();