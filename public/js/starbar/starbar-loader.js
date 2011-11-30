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
    
    var sayso = window.sayso,
        starbarContainer = document.getElementById('sayso-starbar'),
        
        urlMatchPrepend = '^(?:http|https){1}://(?:[\\w.-]+)?',
        currentUrl = window.location.href,
        inIframe = (top !== self);
    
    // setup global "safe" logging functions
    if (!sayso.log) {
        function _log (type) { // <-- closure here allows re-use for log() and warn()
            return function () {
                if (sayso.debug && typeof window.console !== 'undefined' && typeof window.console.log !== 'undefined') {
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
            };
        };
        sayso.log = _log('log'); 
        sayso.warn = _log('warn');
    }
    
    // bring in namespaced jQuery $SQ 
    
    if (!window.$SQ) {
        if (!sayso.loading || sayso.loading !== 'jquery') {
            var jsJQuery = document.createElement('script'); 
            jsJQuery.src = '//' + sayso.baseDomain + '/js/starbar/jquery-1.6.1.min.js';
            starbarContainer.appendChild(jsJQuery);
        }
    }
    

	// Support for stupid browsers

	function getInternetExplorerVersion() {
		var rv = -1; // Return value assumes failure.
		if (navigator.appName == 'Microsoft Internet Explorer') {
			var ua = navigator.userAgent;
			var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
			if (re.exec(ua) != null)
				rv = parseFloat(RegExp.$1);
		}
		return rv;
	}

	function getGeckoVersion() {
		var rv = -1; // Return value assumes failure.
		fullVersion = navigator.userAgent.replace(/^Mozilla.*rv:|\).*$/g, '' ) || ( /^rv\:|\).*$/g, '' );
		if (fullVersion) {
			rv = fullVersion.substring(0,3);
		}
		return rv;
	}

	var ieVersion = getInternetExplorerVersion();
	var geckoVersion = getGeckoVersion();
	if (ieVersion > -1 && ieVersion < 9) {
		sayso.disableJqueryEffects = true;
	} else {
		sayso.disableJqueryEffects = false;
	}

	// test if HTML5 placeholder is supported or not
	if (ieVersion > -1 || (geckoVersion > -1 && geckoVersion < 4)) {
		sayso.placeholderSupportMissing = true;
	} else {
		sayso.placeholderSupportMissing = false;
	}

    
    new jsLoadTimer().start('window.$SQ', function () {
        
        $SQ.jsLoadTimer = jsLoadTimer;
        $SQ.cssLoadTimer = cssLoadTimer;
        
        // JSON support for stupid browsers

        if (!window.JSON || !window.JSON.stringify || !window.JSON.parse) {
            var jsJson = document.createElement('script'); 
            jsJson.src = '//' + sayso.baseDomain + '/js/starbar/json2.min.js';
            starbarContainer.appendChild(jsJson);
        }
        
        // ADjuster 
        
        var jsSayso = document.createElement('script'); 
        jsSayso.src = '//' + sayso.baseDomain + '/js/starbar/sayso.js';
        starbarContainer.appendChild(jsSayso);
        
        // App loading
        
        if (!inIframe) {
            
            // client site detection
            
            var clientSetup = document.createElement('script'); 
            clientSetup.src = '//' + sayso.baseDomain + '/js/starbar/client-setup.js';
            starbarContainer.appendChild(clientSetup);
            
            // start loading app
            
            var clientSetupTimer = new jsLoadTimer();
            clientSetupTimer.start('window.saysoClientSetup', function () {
                
                if (sayso.starbar.id) { // load known Starbar 
                    
                    loadStarbar();
                    
                } else {                // install new Starbar
                
                    sayso.log('****** Installing NEW App ******');
                    
                    var postInstallUrl = '//' + sayso.baseDomain + '/starbar/remote/post-install-setup';
                    
                    if (sayso.client && sayso.client.uuid) { // we must be on a client site, so provide client vars
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
                    iframe.width = '0';
                    iframe.height = '0';
                    iframe.scrolling = 'no';
                    iframe.style.cssText = 'width: 0px; height: 0px; border: 0px;';
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
        }
    
    });
    
    function loadStarbar () {
        
        // bring in the Starbar
            
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
        
		if (sayso.disableJqueryEffects) {
			$SQ.fx.off = true;
		}

        $SQ.ajax({
            dataType: 'jsonp',
            data : params,
            url : '//' + sayso.baseDomain + '/starbar/remote',
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
                sayso.log(starbar.label + ' App', starbar);
                
                sayso.starbar.id = starbar.id;
                sayso.starbar.shortName = starbar.short_name;
                sayso.starbar.authKey = starbar.auth_key;
                sayso.starbar.user.id = starbar._user.id;
                sayso.starbar.user.key = starbar._user._key;
                if (response.game) {
                    sayso.starbar.game = response.game;
				}
                
                // sayso.flags can be used anywhere via sayso.flags.match('<flag_name>')
                // see starbar table, flags column
                if (starbar.flags) {
                    sayso.flags = starbar.flags;
                } else {
                    sayso.flags = 'none';
                }
                
                // update global/persistent vars on kobj.net
                var app = KOBJ.get_application(sayso.starbar.kynetxAppId);
                app.raise_event(
                    'update_global_variables', 
                    { 
                        'starbar_id' : starbar.id, 
                        'auth_key' : starbar.auth_key,
                        'user_id' : starbar._user.id,
                        'user_key' : starbar._user._key,
                        'flags' : sayso.flags
                    }
                );
                
                if (!starbar._html.length) return; // for some reason, no markup was returned
                
                // starbar display conditions
                
                var whiteList = ['facebook.com/pages/SaySo'];
                
                if (window.opener) {
                    var skip = true;
                    for (var i = 0; i < whiteList.length; i++) {
                        if (currentUrl.match(urlMatchPrepend + whiteList[i])) {
                            skip = false;
                            break;
                        }
                    }
                    if (skip) {
                        // do not load starbar for this page
                        sayso.log('Popup detected');
                        return;
                    }
                }
                
                var blackList = [
                    'facebook.com/dialog', 'facebook.com/plugins', 'twitter.com/intent', 'twitter.com/widgets', 
                    'stumbleupon.com/badge', 'reddit.com/static', 'static.addtoany.com/menu',
                    'plusone.google.com', 'intensedebate/empty',
                    '(?:sayso.com|saysollc.com)/html/communicator', '(?:sayso.com|saysollc.com)/starbar'
                ];
                
                var bi = 0;
                for (; bi < blackList.length; bi++) {
                    if (currentUrl.match(urlMatchPrepend + blackList[bi])) {
                        // do not load starbar for this page
                        sayso.log('Blacklisted: ' + blackList[bi] + ' - Not loading Starbar');
                        return;
                    }
                }
                
                new jsLoadTimer().start(function () { return bi === blackList.length; }, function () {
                    
                    // ===========================================
                    // Begin handling the visible console
                    
					// fix FLASH elements!
					$SQ('embed[src*=".swf"], param[name="movie"]').each(function(index) {
						var newElem = null;
						$SQembed = $SQ(this);
						$SQparent = $SQ(this).parent();

						if ($SQembed.is('embed')) {
							$SQembed.css('z-index', '9998 !important');
							if ($SQembed.attr('wmode') != 'transparent' && $SQembed.attr('wmode') != 'opaque') {
								$SQembed.attr('wmode', 'transparent');
								newElem = $SQembed.clone(true, true);
								$SQembed.replaceWith(newElem);
							}
						} else if ($SQembed.is('param')) {
							if (! $SQembed.attr('value').match("\.swf")) return;
						}

						if ($SQparent.is('object')) {
							$SQwmodeParam = $SQ('param[name="wmode"]', $SQparent);
							if ($SQwmodeParam.length == 1) {
								if ($SQwmodeParam.attr('value') != 'transparent' && $SQwmodeParam.attr('value') != 'opaque') {
									$SQwmodeParam.attr('value', 'transparent');
								}
							} else {
								$SQparent.append('<param name="wmode" value="transparent" />');
							}
							$SQparent.css('z-index', '9998 !important');
							
							newElem = $SQparent.clone(true, true);
							$SQparent.replaceWith(newElem);
						} else if ($SQembed.is('embed')) {
						}
					});

                    // bring in the GENERIC CSS
                    
                    var cssGeneric = document.createElement('link'); 
                    cssGeneric.rel = 'stylesheet';
                    cssGeneric.href = '//' + sayso.baseDomain + '/css/starbar-generic.css';
                    starbarContainer.appendChild(cssGeneric);
                    
                    // load JS dependencies
                    
                    var jsUi = document.createElement('script'); 
                    jsUi.src = '//' + sayso.baseDomain + '/js/starbar/jquery-ui-1.8.16.custom.min.js';
                    starbarContainer.appendChild(jsUi);
                    
                    var jsScroll = document.createElement('script'); 
                    jsScroll.src = '//' + sayso.baseDomain + '/js/starbar/jquery.jscrollpane.min.js';
                    starbarContainer.appendChild(jsScroll);
                    
                    var jsCookie = document.createElement('script'); 
                    jsCookie.src = '//' + sayso.baseDomain + '/js/starbar/jquery.cookie.js';
                    starbarContainer.appendChild(jsCookie);
                    
                    var jsJeip = document.createElement('script'); 
                    jsJeip.src = '//' + sayso.baseDomain + '/js/starbar/jquery.jeip.js';
                    starbarContainer.appendChild(jsJeip);
                    
                    var jsEasyTooltip = document.createElement('script'); 
                    jsEasyTooltip.src = '//' + sayso.baseDomain + '/js/starbar/jquery.easyTooltip.js';
                    starbarContainer.appendChild(jsEasyTooltip);
                    
                    var jsCycle = document.createElement('script'); 
                    jsCycle.src = '//' + sayso.baseDomain + '/js/starbar/jquery.cycle.lite.js';
                    starbarContainer.appendChild(jsCycle);
                    
                    var jsEasyXDM = document.createElement('script'); 
                    jsEasyXDM.src = '//' + sayso.baseDomain + '/js/starbar/easyXDM.min.js';
                    starbarContainer.appendChild(jsEasyXDM);
                    
                    // load SaySo Shared Javascript (which depends on the above data settings)
                    var jsSaysoShared = document.createElement('script'); 
                    jsSaysoShared.src = '//' + sayso.baseDomain + '/js/starbar/sayso-shared.js';
                    starbarContainer.appendChild(jsSaysoShared);
                    
                    // load the specific CSS for this Starbar
                    var cssStarbar = document.createElement('link'); 
                    cssStarbar.rel = 'stylesheet';
                    cssStarbar.href = starbar._css_url;
                    starbarContainer.appendChild(cssStarbar);
                    
                    // append the HTML to the DOM
                    var customCssLoadTimer = new cssLoadTimer(); 
                    customCssLoadTimer.start('_sayso_starbar_css_loaded', function () { 
                        
                        
                        var jQueryLibraryLoadTimer = new jsLoadTimer();
                        jQueryLibraryLoadTimer.start('window.jQueryUILoaded', function () {
                            
                            // finally, inject the HTML!
                            $SQ('#sayso-starbar').append(starbar._html);
                            
                            // load Starbar Javascript (which depends on the above data settings)
                            var jsStarbar = document.createElement('script'); 
                            jsStarbar.src = '//' + sayso.baseDomain + '/js/starbar/starbar-new.js';
                            starbarContainer.appendChild(jsStarbar);
                            
                            var starbarJsTimer = new jsLoadTimer();
                            starbarJsTimer.start('window.sayso.starbar.loaded', function () {
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
                                            url : '//' + sayso.baseDomain + '/api/starbar/set-onboard-status',
                                            success : function (response, status) {
                                                sayso.log('Onboarding complete.', response.data);
                                            }
                                        });
                                    });
                                }
                            });
                        });
                    }); // end CSS load timer
                }, 300);
            } // end of success callback
        }); // end $SQ.ajax() /remote/index
    } // end loadStarbar()
    
    // functions to control load order
    
    function jsLoadTimer () { 
        
        var _counter = 0,
            _maxCount = 400, // # of reps X wait time in milliseconds
            _waitTime = 50,
            _symbol = '',
            _callback = null,
            _elseCallback = null,
            _timeout = null,
            _instance = this,
            ref = null;
        
        function _checkAgain () {
            if (_counter++ <= _maxCount) {
                _timeout = setTimeout(_waitUntilJsLoaded, _waitTime);
            } else {
                if (typeof _elseCallback === 'function') {
                    _elseCallback();
                }
            }
        }
        function _waitUntilJsLoaded () {
            try {
                if ((typeof _symbol === 'function' && _symbol()) || (typeof _symbol === 'string' && eval(_symbol))) {
                    if (_timeout) clearTimeout(_timeout);
                    try {
                        _callback();
                    } catch (exception) {
                        sayso.warn(exception);
                    }
                    return;
                } else {
                    _checkAgain();
                }
            } catch (exception) { 
                _checkAgain();
            } 
        }
        this.setMaxCount = function (max) {
            _maxCount = max;
            return this;
        };
        this.setInterval = function (interval) {
            _waitTime = interval;
            return this;
        };
        this.setLocalReference = function (reference) {
            ref = reference;
            return this;
        };
        this.start = function (symbol, callback, elseCallback) {
            _symbol = symbol;
            _callback = callback;
            _elseCallback = elseCallback;
            _waitUntilJsLoaded();
            return this;
        };
    }
    
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
})();
