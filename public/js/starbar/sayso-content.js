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
	sayso.fn = sayso.fn || {};
	sayso.fn.evalInPageContext = function evalInPageContext( arg ) {
		var scriptEl = document.createElement('script');
		if( typeof arg === "function" )
			scriptEl.text = '(' + arg + ')();';
		else
			scriptEl.text = arg;
		document.body.appendChild(scriptEl);
		document.body.removeChild(scriptEl);
	}

	sayso.fn.loadScript = function loadScript(scriptName, onSuccess) {
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

	sayso.fn.setLocalStateFromBackground = function setLocalStateFromBackground( response ) {
		sayso.state = response;
		sayso.starbar = response.starbars[response.currentStarbar];
		sayso.starbar.user = response.user;
		sayso.starbar.state = {visibility: sayso.state.starbarVisibility, profile: sayso.state.profileTS, game: sayso.state.gameTS};
		sayso.starbar.game = sayso.state.economies[sayso.starbar.economyId].game;

		sayso.starbar.economy = sayso.state.economies[sayso.starbar.economyId];
		
		sayso.notifications = [];
	}
	
	sayso.fn.ajaxWithAuth = function ajaxWithAuth(options) {
		options.data = $SQ.extend(options.data || {}, {
			renderer : 'json',
			starbar_id : sayso.starbar.id,
			user_id : sayso.starbar.user.id,
			user_key : sayso.starbar.user.key
		});

		if (!options.dataType)
			options.dataType = 'json';

		if( sayso.topFrameId ) {
			options.data.top_frame_id = sayso.topFrameId;
		}

		options.url = 'http:' + options.url;
		return forge.request.ajax(options);
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
	
	if( sayso.location.host == sayso.baseDomain && sayso.location.pathname == '/starbar/content/close-window' ) {
		if( sayso.location.search.indexOf('update_notifications') >= 0 )
			forge.message.broadcastBackground('update-notifications');
	}
	
	sayso.debug = true;
	sayso.log = safeLog('log', sayso.debug);
	sayso.warn = safeLog('warn', sayso.debug);
	
	sayso.frameId = Math.floor(Math.random()*2e9) + 1;
	sayso.current_url = sayso.location.href;
	sayso.in_iframe = forge.is.firefox() ? (unsafeWindow.window !== unsafeWindow.top) : (window.top != window);
	if( !sayso.in_iframe ) {
		sayso.topFrameId = sayso.frameId;
		function handleParentReq( event ) {
			var prefix = 'sayso-parent-req-';
			if( event.data.slice(0, prefix.length) == prefix )
				forge.message.broadcast('parent-location-' + event.data.slice(prefix.length)
					, { location: sayso.location, frameId: sayso.frameId });
		}
		if( window.addEventListener )
			window.addEventListener('message', handleParentReq);
		else if( window.attachEvent )
			window.attachEvent('onmessage', handleParentReq);
	}

	sayso.fn.loadScript('starbar/jquery-1.7.1.min.js', jQueryLoaded);
	sayso.url_match_prepend = '^(?:http|https){1}://(?:[\\w.-]+[.])?';
	sayso.ie_version = getInternetExplorerVersion();
	
	function jQueryLoaded() {
		$SQ(function(){
			sayso.fn.evalInPageContext( 'window.$SaySoExtension = {};' );
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
						sayso.fn.loadScript('surveygizmo/content.js');
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
				sayso.fn.setLocalStateFromBackground(response);

				sayso.flags = 'none';
				
				var missionShortName;

				// ADjuster can run asynchronously
				if( sayso.in_iframe ) {
					forge.message.listen('parent-location-' + sayso.frameId, function( m ) {
						if( !sayso.parentLocation ) {
							sayso.parentLocation = m.location;
							sayso.topFrameId = m.frameId;
							sayso.fn.loadScript('starbar/sayso.js');
						}
					});
					function requestParentLocation() {
						if( !sayso.parentLocation ) {
							sayso.fn.evalInPageContext( "top.postMessage( 'sayso-parent-req-" + sayso.frameId + "', '*' );");
							setTimeout(requestParentLocation, 200);
						}
					}
					requestParentLocation();
				} else if( missionShortName = sayso.location.href.match(/(?:.say.so|.saysollc.com\/.*)\/mission\/(.*)\//)) {
					missionShortName = missionShortName[1];
					function handleMissionProgress( event ) {
						var data;
						try {
							data = JSON.parse(event.data);
						} catch( e ) {
							return;
						}
						var prefix = 'sayso-mission-progress';
						if( data[0] && data[0].slice && data[0].slice(0, prefix.length) == prefix ) {
							data = data[1];
							sayso.fn.ajaxWithAuth( {
								url: '//'+sayso.baseDomain+'/api/survey/user-mission-submit',
								data: {
									mission_short_name: missionShortName,
									mission_data: data
								},
								type : 'POST',
								success: function(response) {
									if( response.status && response.status == 'success') {
										if( response.game )
											forge.message.broadcastBackground( 'update-game', response.game );
										if( data.stage == data.data.stages.length )
											forge.message.broadcastBackground('mission-complete');
									}
								}
							});
						}
					}
					if( window.addEventListener )
						window.addEventListener('message', handleMissionProgress);
					else if( window.attachEvent )
						window.attachEvent('onmessage', handleMissionProgress);
						return; // DO NOT LOAD STARBAR
				} else
					sayso.fn.loadScript('starbar/sayso.js');
				
				// Only load starbar if conditions are met
				if( shouldLoadStarbar() )
					sayso.fn.loadScript('starbar/starbar-loader.js');
				
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
			'facebook.com/connect', 'api.twitter.com/oauth/authorize',
			'stumbleupon.com/badge', 'reddit.com/static', 'static.addtoany.com/menu',
			'plusone.google.com', 'intensedebate/empty',
			'mail.google.com',
			'saysollc.com/html/communicator', 'saysollc.com/starbar'
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
})();
