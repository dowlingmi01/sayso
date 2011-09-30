/**
 * Starbar Loader
 * 
 * Choreographs the order of dependencies for a Starbar to load 
 * correctly, including: setting client vars (via detecting if we are on a client site), 
 * loading jQuery, generic CSS, jQuery UI + libs, fetcing the Starbar,
 * behavorial (sayso) JS, custom CSS and finally injecting the Starbar markup
 * 
 * @author davidbjames
 */
(function () {
    
    // setup global "safe" logging functions
    window.sayso.log = _log('log'); 
    window.sayso.warn = _log('warn');
    function _log (type) { // <-- closure here allows re-use for log() and warn()
        return function () {
            if (window.sayso.debug && typeof window.console !== 'undefined' && typeof window.console.log !== 'undefined') {
                var args = Array.prototype.slice.call(arguments);
                if (typeof console.log.apply === 'function') {
                    args.unshift('SaySo:');
                    window.console[type].apply(window.console, args);
                } else {
                    // must be IE
                    if (typeof args[0] !== 'object') {
                        window.console.log(args[0]);
                    }
                }
            }
        }
    };
    
    var sayso = window.sayso;
    
    var starbarContainer = document.getElementById('sayso-starbar');
    
    sayso.log('Starbar initializing');
    sayso.log(sayso.starbar);
    
    // detect client site (and setup client vars)
    
    var clientSetup = document.createElement('script'); 
    clientSetup.src = 'http://' + sayso.baseDomain + '/js/starbar/client-setup.js';
    starbarContainer.appendChild(clientSetup);
    
    // bring in the GENERIC CSS
    
    var css1 = document.createElement('link'); 
    css1.rel = 'stylesheet';
    css1.href = 'http://' + sayso.baseDomain + '/css/starbar-generic.css';
    starbarContainer.appendChild(css1);

    // bring in namespaced jQuery $SQ 
    
    if (!window.hasOwnProperty('$SQ')) {
        var js1 = document.createElement('script'); 
        js1.src = 'http://' + sayso.baseDomain + '/js/starbar/jquery-1.6.1.min.js';
        starbarContainer.appendChild(js1);
    }
    
    var clientSetupTimer = new jsLoadTimer();
    clientSetupTimer.start('saysoClientSetup', function () {
        
        if (sayso.starbar.id) { // load known Starbar 
            
            sayso.log('Starbar ID: ' + sayso.starbar.id);
            loadStarbar();
            
        } else {                // install new Starbar
        
            sayso.log('****** Installing NEW Starbar ******');
            
            var postInstallUrl = 'http://' + sayso.baseDomain + '/starbar/remote/post-install-setup';
            
            if (sayso.client) { // we must be on a client site, so provide client vars
                postInstallUrl += 
                    '?client_name=' + sayso.client.name + 
                    '&client_uuid=' + sayso.client.uuid + 
                    '&client_uuid_type=' + sayso.client.uuidType +
                    '&client_user_logged_in=' + (sayso.client.userLoggedIn ? 'true' : '');
            }
            
            // load iframe to pass pre-install cookies to sayso
            // along with IP and user agent headers
            var iframe = document.createElement('iframe');
            iframe.src = postInstallUrl;
            iframe.width = '0'; iframe.height = '0'; iframe.scrolling = '0';
            iframe.style = 'width: 0; height: 0; border: none;';
            starbarContainer.appendChild(iframe);
            
            // after a short delay, continue loading starbar
            // NOTE: because the Starbar ID is not established at this point
            // the action to retreive the starbar (/starbar/remote) will
            // be routed to /starbar/remote/post-install-deliver which will
            // match the IP/user agent from the previous step and lookup the 
            // correct Starbar for this user (e.g. HelloMusic)
            setTimeout(loadStarbar, 1000);
        } 
    });
    
    // functions to control load order
    
    function jsLoadTimer (maxCount, waitTime) {
        
        var _counter = 0,
            _maxCount = maxCount || 200, // about 10 seconds (200 x 50 mseconds for each timer)
            _waitTime = waitTime || 50,
            _searchSymbol = '',
            _callback = null;
        
        function _waitUntilJsLoaded () {
            try {
                if (eval(_searchSymbol)) {
                    _callback();
                    return;
                }
            } catch (exception) {} // ignore
            
            if (_counter++ > _maxCount) { 
                _callback(); // stop waiting and just fire the callback
                return;
            }
            setTimeout(_waitUntilJsLoaded, _waitTime);
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
        starbarContainer.appendChild(_testCssLoaded);
    
        function _waitUntilCssLoaded () {
            if ($SQ(_testCssLoaded).css('width') === '1px') {
                $SQ(_testCssLoaded).css('width', '0px');
                _callback();
                return;
            }
            if (_counter++ > _maxCount) { 
                _callback(); // stop waiting and just fire it
                sayso.log('Stopped waiting for CSS to load (not loaded: ' + _testCssLoaded.className + ') ');
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
   
    function loadStarbar () {
        
        // bring in the Starbar
        var jQueryLoadTimer = new jsLoadTimer();
        jQueryLoadTimer.start('$SQ', function () {
            sayso.log('Loaded - jQuery, generic CSS');
            
            // load auxilliary (namespaced) JS libraries
            var js2 = document.createElement('script'); 
            js2.src = 'http://' + sayso.baseDomain + '/js/starbar/jquery-ui-1.8.16.custom.min.js';
            starbarContainer.appendChild(js2);
            
            var js3 = document.createElement('script'); 
            js3.src = 'http://' + sayso.baseDomain + '/js/starbar/jquery.jscrollpane.min.js';
            starbarContainer.appendChild(js3);
            
            var js4 = document.createElement('script'); 
            js4.src = 'http://' + sayso.baseDomain + '/js/starbar/jquery.cookie.js';
            starbarContainer.appendChild(js4);
            
            var url = 'http://' + sayso.baseDomain;
            
            var params = {
                starbar_id : sayso.starbar.id,
                auth_key : sayso.starbar.authKey,
                user_id : sayso.starbar.user.id,
                user_key : sayso.starbar.user.key,
                visibility : sayso.starbar.state.visibility
            };
            
            if (sayso.client) { // we must be on the customer's page
                params.client_name = sayso.client.name;
                params.client_uuid = sayso.client.uuid;
                params.client_uuid_type = sayso.client.uuidType;
                params.client_user_logged_in = (sayso.client.userLoggedIn ? 'true' : '');
            }
            
            $SQ.ajax({
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
                    sayso.log('Received - ' + starbar.label + ' Starbar');
                    sayso.log(starbar);
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
                    starbarContainer.appendChild(js5);
                    
                    // load the specific CSS for this Starbar
                    var css2 = document.createElement('link'); 
                    css2.rel = 'stylesheet';
                    css2.href = starbar._css_url;
                    starbarContainer.appendChild(css2);
                    
                    // append the HTML to the DOM
                    var customCssLoadTimer = new cssLoadTimer(); 
                    customCssLoadTimer.start('_sayso_starbar_css_loaded', function () { 
                        sayso.log('Loaded - custom CSS');
                        
                        var jQueryLibraryLoadTimer = new jsLoadTimer();
                        jQueryLibraryLoadTimer.start('jQueryUILoaded', function () {
                            sayso.log('Loaded - jQuery libs (incl. jQueryUI)');
                            
                            // finally, inject the HTML!
                            $SQ('#sayso-starbar').append(starbar._html);
                            
                            // load SaySo Javascript (which depends on the above data settings)
                            var js6 = document.createElement('script'); 
                            js6.src = 'http://' + sayso.baseDomain + '/js/starbar/starbar-new.js';
                            starbarContainer.appendChild(js6);
                            
                            var starbarJsTimer = new jsLoadTimer();
                            starbarJsTimer.start('window.sayso.starbar.loaded', function () {
                                // if user has not "onboarded" and we are on the Starbar's base domain
                                // then trigger the onboarding to display
                                var href = window.location.href;
                                if (!starbar._user_map.onboarded && (href.match(starbar.domain) || href.match('saysollc.com') || href.match('sayso.com'))) {
                                    // trigger onboarding to display (see starbar-new.js where this is handled)
                                    setTimeout(function () { $SQ(document).trigger('onboarding-display'); }, 2500);
                                    // bind when the last step of the onboarding is selected, to mark onboarding done
                                    // see starbar-new.js where this is triggered
                                    $SQ(document).bind('onboarding-complete', function () { 
                                        $SQ.ajax({
                                            dataType: 'jsonp',
                                            data : {
                                                starbar_id : sayso.starbar.id,
                                                auth_key : sayso.starbar.authKey,
                                                user_id : sayso.starbar.user.id,
                                                user_key : sayso.starbar.user.key,
                                                renderer : 'jsonp',
                                                status : 1 // complete
                                            },
                                            url : 'http://' + sayso.baseDomain + '/api/starbar/set-onboard-status',
                                            success : function (response, status) {
                                                sayso.log('Onboarding complete.', response.data);
                                            }
                                        });
                                    });
                                }
                            });
                        });
                    });
                }
            }); // end $SQ.ajax()
        }); // end jQueryLoadTimer
    } // end loadStarbar()
})();
