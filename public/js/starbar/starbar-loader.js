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
    
    var urlMatchPrepend = '^(?:http|https){1}://(?:[\\w.-]+)?',
        currentUrl = window.location.href;
    
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
    if (window.opener) {
        sayso.log('This is a popup. Starbar will not be loaded.');
        return;
    }
    var skipList = ['facebook.com/dialog', 'twitter.com/intent'];
    
    for (var i = 0; i < skipList.length; i++) {
        if (currentUrl.match(urlMatchPrepend + skipList[i])) {
            // do not load starbar for this page
            return;
        }
    }
    
    var starbarContainer = document.getElementById('sayso-starbar');
    
    sayso.log('Starbar initializing');
    sayso.log(sayso.starbar);
    
    // detect client site (and setup client vars)
    
    var clientSetup = document.createElement('script'); 
    clientSetup.src = 'http://' + sayso.baseDomain + '/js/starbar/client-setup.js';
    starbarContainer.appendChild(clientSetup);
    
    // bring in the GENERIC CSS
    
    var cssGeneric = document.createElement('link'); 
    cssGeneric.rel = 'stylesheet';
    cssGeneric.href = 'http://' + sayso.baseDomain + '/css/starbar-generic.css';
    starbarContainer.appendChild(cssGeneric);

    // bring in namespaced jQuery $SQ 
    
    if (!window.hasOwnProperty('$SQ')) {
        var jsJQuery = document.createElement('script'); 
        jsJQuery.src = 'http://' + sayso.baseDomain + '/js/starbar/jquery-1.6.1.min.js';
        starbarContainer.appendChild(jsJQuery);
    }
    
    var clientSetupTimer = new jsLoadTimer();
    clientSetupTimer.start('window.saysoClientSetup', function () {
        
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
            iframe.style.width = '0'; iframe.style.height = '0'; iframe.style.border = 'none';
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
    
    function jsLoadTimer () {
        
        var _counter = 0,
            _maxCount = 400, // about 20 seconds (400 x 50 mseconds for each timer)
            _waitTime = 50,
            _symbol = '',
            _callback = null,
            _timeout = null,
            _instance = this;
        
        function _waitUntilJsLoaded () {
            try {
                if (eval(_symbol)) {
                    if (_timeout) clearTimeout(_timeout);
                    _callback();
                    return;
                }
            } catch (exception) {} // ignore
            
            if (_counter++ > _maxCount) {
                clearTimeout(_timeout);
                return;
            }
            _timeout = setTimeout(_waitUntilJsLoaded, _waitTime);
        }
        this.start = function (symbol, callback) {
            _symbol = symbol;
            _callback = callback;
            _waitUntilJsLoaded();
        };
    };
    
    function cssLoadTimer () {
        
        var _counter = 0,
            _maxCount = 200,
            _callback = null;
        
        // create & append a test div to the dom
        var _testCssLoaded = document.createElement('div');
        _testCssLoaded.style.display = 'none';
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
        jQueryLoadTimer.start('window.$SQ', function () {
            sayso.log('Loaded - jQuery, generic CSS');
            
            // load auxilliary (namespaced) JS libraries
            var jsUi = document.createElement('script'); 
            jsUi.src = 'http://' + sayso.baseDomain + '/js/starbar/jquery-ui-1.8.16.custom.min.js';
            starbarContainer.appendChild(jsUi);
            
            var jsScroll = document.createElement('script'); 
            jsScroll.src = 'http://' + sayso.baseDomain + '/js/starbar/jquery.jscrollpane.min.js';
            starbarContainer.appendChild(jsScroll);
            
            var jsCookie = document.createElement('script'); 
            jsCookie.src = 'http://' + sayso.baseDomain + '/js/starbar/jquery.cookie.js';
            starbarContainer.appendChild(jsCookie);
            
            var jsJeip = document.createElement('script'); 
            jsJeip.src = 'http://' + sayso.baseDomain + '/js/starbar/jquery.jeip.js';
            starbarContainer.appendChild(jsJeip);
            
            var jsColorBox = document.createElement('script'); 
            jsColorBox.src = 'http://' + sayso.baseDomain + '/js/starbar/jquery.colorbox-min.js';
            starbarContainer.appendChild(jsColorBox);
            
            var jsEasyTooltip = document.createElement('script'); 
            jsEasyTooltip.src = 'http://' + sayso.baseDomain + '/js/starbar/jquery.easyTooltip.js';
            starbarContainer.appendChild(jsEasyTooltip);
            
            var jsEasyXDM = document.createElement('script'); 
            jsEasyXDM.src = 'http://' + sayso.baseDomain + '/js/starbar/easyXDM.min.js';
            starbarContainer.appendChild(jsEasyXDM);
            
            // load SaySo Shared Javascript (which depends on the above data settings)
            var jsSaysoShared = document.createElement('script'); 
            jsSaysoShared.src = 'http://' + sayso.baseDomain + '/js/starbar/sayso-shared.js';
            starbarContainer.appendChild(jsSaysoShared);
            
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
                        sayso.warn(response.data);
                        return;
                    }
                    
                    // load server data for this Starbar
                    var starbar = response.data;
                    sayso.log('Received - ' + starbar.label + ' Starbar');
                    sayso.log(starbar);
                    sayso.starbar.id = starbar.id;
                    sayso.starbar.shortName = starbar.short_name;
                    sayso.starbar.authKey = starbar.auth_key;
                    sayso.starbar.user.id = starbar._user.id;
                    sayso.starbar.user.key = starbar._user._key;
                    
                    if (response.gamer && response.gamer.type === 'Gamer') {
                        sayso.starbar.user.gaming = response.gamer;
                    }
                    
                    // update global/persistent vars on kobj.net
                    var app = KOBJ.get_application(sayso.starbar.kynetxAppId);
                    app.raise_event(
                        'update_global_variables', 
                        { 
                            'starbar_id' : starbar.id, 
                            'auth_key' : starbar.auth_key,
                            'user_id' : starbar._user.id,
                            'user_key' : starbar._user._key
                        }
                    );
                    
                    // load SaySo Javascript (which depends on the above data settings!)
                    var jsSayso = document.createElement('script'); 
                    jsSayso.src = 'http://' + sayso.baseDomain + '/js/starbar/sayso.js';
                    starbarContainer.appendChild(jsSayso);
                    
                    // load the specific CSS for this Starbar
                    var cssStarbar = document.createElement('link'); 
                    cssStarbar.rel = 'stylesheet';
                    cssStarbar.href = starbar._css_url;
                    starbarContainer.appendChild(cssStarbar);
                    
                    // append the HTML to the DOM
                    var customCssLoadTimer = new cssLoadTimer(); 
                    customCssLoadTimer.start('_sayso_starbar_css_loaded', function () { 
                        sayso.log('Loaded - custom CSS');
                        
                        var jQueryLibraryLoadTimer = new jsLoadTimer();
                        jQueryLibraryLoadTimer.start('window.jQueryUILoaded', function () {
                            sayso.log('Loaded - jQuery libs (incl. jQueryUI)');
                            
                            // finally, inject the HTML!
                            $SQ('#sayso-starbar').append(starbar._html);
                            
                            // load Starbar Javascript (which depends on the above data settings)
                            var jsStarbar = document.createElement('script'); 
                            jsStarbar.src = 'http://' + sayso.baseDomain + '/js/starbar/starbar-new.js';
                            starbarContainer.appendChild(jsStarbar);
                            
                            var starbarJsTimer = new jsLoadTimer();
                            starbarJsTimer.start('window.sayso.starbar.loaded', function () {
                                sayso.log('Onboarding status [user:' + sayso.starbar.user.id + ']', (starbar._user_map.onboarded ? 'ONBOARDED' : 'NOT ONBOARDED'));
                                if (currentUrl.match(urlMatchPrepend + starbar.domain)) {
                                    sayso.log('Onboard URL match [' + starbar.domain + ']', currentUrl);
                                } else if (currentUrl.match(urlMatchPrepend + 'saysollc.com')) {
                                    sayso.log('Onboard URL match [saysollc.com]', currentUrl);
                                } else if (currentUrl.match(urlMatchPrepend + 'sayso.com')) {
                                    sayso.log('Onboard URL match [sayso.com]', currentUrl);
                                } else {
                                    sayso.log('Onboard URL *NO MATCH*', currentUrl);
                                }
                                
                                // if user has not "onboarded" and we are on the Starbar's base domain
                                // then trigger the onboarding to display
                                if (!starbar._user_map.onboarded && 
                                    (
                                        currentUrl.match(urlMatchPrepend + starbar.domain) || 
                                        currentUrl.match(urlMatchPrepend + 'saysollc.com') ||  // also trigger on our domains for testing purposes
                                        currentUrl.match(urlMatchPrepend + 'sayso.com')
                                    )
                                ) {
                                    // trigger onboarding to display (see starbar-new.js where this is handled)
                                    setTimeout(function () { $SQ(document).trigger('onboarding-display'); }, 2000);
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
