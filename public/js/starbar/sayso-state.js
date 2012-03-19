/**
 * Sayso State -- Authenticate before loading starbar
 */
(function () {

	// No extension, no jQuery, or $SQ.sayso has already been loaded
	if (!window.$SaySoExtension || !window.$SQ || window.$SQ.sayso) return;

	// jQuery
	$SQ = window.$SQ;


	// This function was moved from starbar-loader, provides $SQ.sayso.log() and $SQ.sayso.warn()
	function safeLog (type, debug) { // <-- closure here allows re-use for log() and warn()
		return function () {
			if (debug && typeof window.console !== 'undefined' && typeof window.console.log !== 'undefined') {
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

	// Support for stupid browsers
	// These function were moved from starbar-loader
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

	String.prototype.trim = function() {
		return this.replace(/^\s+|\s+$/g,'');
	};

	// setup global "safe" logging functions
	var sayso = {};
	sayso.debug = true;
	sayso.log = safeLog('log', sayso.debug);
	sayso.warn = safeLog('warn', sayso.debug);
	
	sayso.log( "Starting extension version " + window.$SaySoExtension.ext_version + " on " + window.$SaySoExtension.ext_browser );

	sayso.current_url = window.location.href;
	sayso.in_iframe = (top !== self);

	if( window.sayso && window.sayso.client )
		sayso.client = window.sayso.client;
	else
		; // Look at cookies as done in client-setup.js
	
	// The default base domain to use $SQ.sayso.baseDomain (returned by ajax call below)
	// can override the baseDomain for the user, but we need to do the ajax call to somewhere!
	var saysoBaseDomain = window.$SaySoExtension.base_domain;
	
	var ajaxData = { renderer: 'jsonp' };
	
	if( !sayso.in_iframe )
		ajaxData.url = sayso.current_url;
		
	if( sayso.client ) {
		ajaxData.client_name = sayso.client.name;
		if( sayso.client.meta && sayso.client.meta.sendKeys ) {
			var clientKeys = {};
			for( var i = 0; i < sayso.client.meta.sendKeys.length; i++ )
				clientKeys[sayso.client.meta.sendKeys[i]] = getCookie(sayso.client.meta.sendKeys[i]);
			ajaxData.client_keys = clientKeys;
		}
	}
	
	$SQ.ajax({
		dataType: 'jsonp',
		url: '//' + saysoBaseDomain + '/api/user-state/get',
		data: ajaxData,
		success: function (response) {
			sayso.installed = true;
			sayso.url_match_prepend = '^(?:http|https){1}://(?:[\\w.-]+)?';
			sayso.ie_version = getInternetExplorerVersion();
			sayso.gecko_version = getGeckoVersion();

			if (response.status && response.status == "error") {
				sayso.warn("Error getting user-state: ", response.data);
			} else {
				sayso.baseDomain = response.data.base_domain;
				sayso.flags = response.data.flags;
				sayso.starbar = {
					id : response.data.starbar_id,
					authKey : response.data.auth_key,
					user : {
						id : response.data.user_id,
						key : response.data.user_key
					},
					state : {
						visibility : response.data.visibility,
						profile : response.data.last_update_profile,
						game : response.data.last_update_game
					},
					loaded : false
				};

				sayso.study = {
					studies : response.data.studies,
					studiesTimestamp : response.data.last_update_studies,
					adTargets : response.data.ad_targets
				}
				
				$SQ.sayso = sayso;
				
				$SQ.jsLoadTimer = jsLoadTimer;

				// ADjuster can run asynchronously
				var jsSayso = document.createElement('script');
				jsSayso.src = '//' + sayso.baseDomain + '/js/starbar/sayso.js';
				document.body.appendChild(jsSayso);
				
				// Only load starbar if not in iframe
				if( !sayso.in_iframe )
					window.$SaySoExtension.loadNextScript('sayso-state.js');
			}

		},
		error: function(jqXHR, textStatus, errorThrown) {
			sayso.warn( "Error getting user-state: " + textStatus + (errorThrown ? " - " + errorThrown : ""));
		}
	});

	// Moved from starbar-loader
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

	function getCookie (find) {
		var cookies = document.cookie.split(';');
		for(var i = 0; i < cookies.length; i++) {
			var name = cookies[i].slice(0, cookies[i].indexOf('=')).trim();
			if (name === find) {
				return cookies[i].slice(cookies[i].indexOf('=')+1).trim();
			}
		}
		return '';
	}

})();
