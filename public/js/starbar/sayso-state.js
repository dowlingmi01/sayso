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



	// $The default base domain to use SQ.sayso.baseDomain (returned by ajax call below) can override the baseDomain for the user, but we need to do the ajax call to somewhere!
	var saysoBaseDomain = window.$SaySoExtension.base_domain;

	// Insert the starbar constainer
 	if ($SQ('#sayso-starbar').length < 1) $SQ('body').append('<div id="sayso-starbar" style="position: fixed; left: 0px; bottom: 0px; width: 100%; background: none; margin-bottom: -3px; z-index: 9999;"></div>');

	$SQ.ajax({
		dataType: 'jsonp',
		url: '//' + saysoBaseDomain + '/api/user-state/get?renderer=jsonp',
		success: function (response, status) {
			console.log(response);

			var sayso = {};
			sayso.debug = true;
			sayso.installed = true;
			sayso.url_match_prepend = '^(?:http|https){1}://(?:[\\w.-]+)?';
			sayso.current_url = window.location.href;
			sayso.in_iframe = (top !== self);
			sayso.ie_version = getInternetExplorerVersion();
			sayso.gecko_version = getGeckoVersion();

			// setup global "safe" logging functions
			sayso.log = safeLog('log', sayso.debug);
			sayso.warn = safeLog('warn', sayso.debug);

			if (response.status && response.status == "error") {
				sayso.baseDomain = saysoBaseDomain;
				sayso.starbar = { id : 0, authKey: 0, user: { id: 0, key: 0 }, state: { visbility: 0 } }
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
			}

			$SQ.sayso = sayso;

			// Whether successful or not, continue loading starbar-loader, since we may be on a client site,
			// and starbar-loader completes the install process
			if (window.$SaySoExtension) window.$SaySoExtension.loadNextScript('sayso-state.js');
		}
	});

})();
