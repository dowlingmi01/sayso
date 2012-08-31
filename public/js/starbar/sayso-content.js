(function() {
	if( !sayso.location )
		try {
			sayso.location = {
				'hash': location.hash,
				'host': location.host,
				'hostname': location.hostname,
				'href': location.href,
				'pathname': location.pathname,
				'port': location.port,
				'protocol': location.protocol,
				'search': location.search
			};
		} catch( e ) {
			forge.logging.error( e );
			return;
		}
	sayso.evalInPageContext = function( arg ) {
		var scriptEl = document.createElement('script');
		if( typeof arg === "function" )
			scriptEl.text = '(' + arg + ')();';
		else
			scriptEl.text = arg;
		document.body.appendChild(scriptEl);
		document.body.removeChild(scriptEl);
	}

	sayso.loadScript = function(scriptName, onSuccess) {
		forge.message.broadcastBackground( "get-script", scriptName
					, function(content) {
						if( window.execScript )
							window.execScript(content)
						else
							eval(content);
						if( onSuccess )
							onSuccess();
					}
					, function(errObject) {
						forge.logging.error(errObject.message);
					}
				);
	}

	sayso.loadExternalScript = function(scriptURL, onSuccess) {
		forge.request.get( scriptURL
					, function(content) {
						content = content + "\n//@ sourceURL=" + scriptURL;
						if( window.execScript )
							window.execScript(content)
						else
							eval(content);
						if( onSuccess )
							onSuccess();
					}
					, function(errObject) {
						forge.logging.error(errObject.message);
					}
				);
	}

	function safeLog (type, debug) { // <-- closure here allows re-use for log() and warn()
		return function () {
			var args = Array.prototype.slice.call(arguments);
			if( forge.is.chrome() || forge.is.safari()) {
				args.unshift('SaySo:');
				window.console[type].apply(window.console, args);
			} else {
				if( args.length == 1 )
					args = args[0];
				forge.logging.log(args);
			}
		};
	};

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

	String.prototype.trim = function() {
		return this.replace(/^\s+|\s+$/g,'');
	};
	
	sayso.debug = true;
	sayso.log = safeLog('log', sayso.debug);
	sayso.warn = safeLog('warn', sayso.debug);
	
	sayso.current_url = window.location.href;
	sayso.in_iframe = forge.is.firefox() ? (unsafeWindow.window !== unsafeWindow.top) : (window.top != window);

	sayso.loadScript('starbar/jquery-1.7.1.min.js', jQueryLoaded);
	sayso.url_match_prepend = '^(?:http|https){1}://(?:[\\w.-]+[.])?';
	sayso.ie_version = getInternetExplorerVersion();

	
	function jQueryLoaded() {
		$SQ.sayso = sayso;
		$SQ.jsLoadTimer = jsLoadTimer;
		$SQ(function(){
			sayso.evalInPageContext( 'window.$SaySoExtension = {};' );
			var frameComm;
			if( sayso.in_iframe && (frameComm = document.getElementById('sayso-frame-comm')) ) {
				function frameCommHandler() {
					$SQ(frameComm).children().each( function() {
						var x = JSON.parse($SQ(this).attr('value'));
						forge.message.broadcast( x.type, x.content );
						$SQ(this).remove();
					});
				};
				if( frameComm.addEventListener )
					frameComm.addEventListener('saysoFrameComm', frameCommHandler);
				else if( frameComm.attachEvent )
					frameComm.attachEvent('onclick', frameCommHandler);
				frameCommHandler();
			}
			if( sayso.location.hostname.match('surveygizmo.com')) {
				function sgqHandler() {
					var sgq = $SQ('#sayso-sgq');
					if( sgq.length ) {
						window.$SGQ = JSON.parse(sgq.attr('value'));
						sgq.remove();
						sayso.loadScript('surveygizmo/content.js');
					}
				};
				
				if( $SQ('#sayso-sgq').length )
					sgqHandler();
				else if( document.addEventListener )
					document.addEventListener('saysoSGQ', sgqHandler);
				else if( document.attachEvent )
					document.attachEvent('onafterupdate', sgqHandler);
			
			}
			forge.message.broadcastBackground( "get-state", {}, function( response ) {
				sayso.state = response;
				sayso.starbar = response.starbars[response.currentStarbar];
				sayso.starbar.user = response.user;
				sayso.starbar.state = {visibility: sayso.state.starbarVisibility, profile: sayso.state.profileTS, game: sayso.state.gameTS};
				sayso.starbar.game = sayso.state.economies[sayso.starbar.economyId].game;

				sayso.starbar.economy = sayso.state.economies[sayso.starbar.economyId];

				sayso.flags = 'none';

				// ADjuster can run asynchronously
				sayso.loadScript('starbar/sayso.js');
				
				// Only load starbar if conditions are met
				if( shouldLoadStarbar() )
					sayso.loadScript('starbar/starbar-loader.js');
				
			});
		});
	}
	// Moved from starbar-loader
	function shouldLoadStarbar() {
		if( sayso.in_iframe )
			return false;
			
		if (!sayso.starbar.html.length) return; // for some reason, no markup was returned
			
		if (window.opener && $SQ(window).width() < 720) { // probably a popup..

			var whiteList = ['facebook.com/pages/SaySo'], // always OK
				popup = true;

			for (var i = 0; i < whiteList.length; i++) {
				if (sayso.current_url.match(sayso.url_match_prepend + whiteList[i])) {
					popup = false;
					break;
				}
			}
			if (popup) {
				// do not load starbar for this page
				sayso.log('Popup detected');
				return false;
			}
		}

		var blackList = [ // never OK
			'facebook.com/dialog', 'facebook.com/plugins', 'facebook.com/login', 'twitter.com/intent', 'twitter.com/widgets',
			'stumbleupon.com/badge', 'reddit.com/static', 'static.addtoany.com/menu',
			'plusone.google.com', 'intensedebate/empty',
			'mail.google.com',
			'(?:sayso.com|saysollc.com)/html/communicator', '(?:sayso.com|saysollc.com)/starbar'
		];

		var bi = 0;
		for (; bi < blackList.length; bi++) {
			if (sayso.current_url.match(sayso.url_match_prepend + blackList[bi])) {
				// do not load starbar for this page
				sayso.log('Blacklisted: ' + blackList[bi] + ' - Not loading Starbar');
				return false;
			}
		}
		
		if ($SQ('embed[type*="pdf"]').length > 0) return false; // Don't load on Google Docs
		
		return true;
	}
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
