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

	// No jQuery, or no authentication
	if (!window.$SQ || !window.$SQ.sayso) return;

	// jQuery
	$SQ = window.$SQ;
	
	// Insert the starbar container
 	if ($SQ('#sayso-starbar').length < 1)
 		$SQ('body').append('<div id="sayso-starbar" style="position: fixed; left: 0px; bottom: 0px; width: 100%; background: none; margin-bottom: -3px; z-index: 9999;"></div>');

	var sayso = $SQ.sayso,
		starbarContainer = document.getElementById('sayso-starbar'),
		currentUrl = $SQ.sayso.current_url,
		urlMatchPrepend = $SQ.sayso.url_match_prepend;

	$SQ.cssLoadTimer = cssLoadTimer;

	// easyXDM for cross-domain iframe communication
	var jsEasyXDM = document.createElement('script');
	jsEasyXDM.src = '//' + sayso.baseDomain + '/js/starbar/easyXDM.min.js';
	starbarContainer.appendChild(jsEasyXDM);

	// load SaySo Shared Javascript (which depends on the above data settings)
	var jsSaysoShared = document.createElement('script');
	jsSaysoShared.src = '//' + sayso.baseDomain + '/js/starbar/sayso-shared.js';
	starbarContainer.appendChild(jsSaysoShared);

	// App loading

	/* Feature checks */
	var ieVersion = sayso.ie_version ;
	var geckoVersion = sayso.gecko_version;

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

	// fix FLASH elements!
	$SQ('embed[src*=".swf"]').each(function(index) {
		$SQembed = $SQ(this);

		if ($SQembed.attr('id') == 'sm2movie') return true; // no fix needed, go to next <embed>

		$SQembed.css('z-index', '9998 !important');
		if ($SQembed.attr('wmode') != 'transparent' && $SQembed.attr('wmode') != 'opaque') {
			$SQembed.attr('wmode', 'transparent');
			newElem = $SQembed.clone(true, true);
			$SQembed.replaceWith(newElem);
		}
	});

	// fix FLASH elements for IE!
	if (ieVersion > -1) {
		$SQ('object').each(function(index) {
			$SQobject = $SQ(this);
			if ($SQobject.attr('id') == 'sm2movie' || $SQobject.attr('id') == 'FS') return true; // no fix needed, go to next <object>
			$SQwmodeParam = $SQ('param[name="wmode"]', $SQobject);
			if ($SQwmodeParam.length == 1) {
				if ($SQwmodeParam.attr('value') == 'transparent' || $SQwmodeParam.attr('value') == 'opaque') {
					return true; // no fix needed, go to next <object>
				} else {
					$SQwmodeParam.attr('value', 'transparent');
				}
			} else {
				// Check if this <object> is flash, if so add the wmode parameter
				$SQmovieParam = $SQ('param[name="movie"]', $SQobject);
				if ($SQmovieParam.length == 1 && $SQmovieParam.attr('value').match(/.swf/)) {
					newParam = document.createElement('param');
					newParam.setAttribute('name', 'wmode');
					newParam.setAttribute('value', 'transparent');
					$SQobject.append(newParam);
				} else {
					return true; // not flash, go to next <object>
				}
			}
			$SQobject.css('z-index', '9998 !important');

			container = $SQobject.parent();
			newElem = $SQobject.clone(true);
			$SQobject.remove();
			container.html(newElem);
		});
	}

	loadStarbar();

	function loadStarbar () {


		if ($SQ('embed[type*="pdf"]').length > 0) return; // Don't load on Google Docs

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
				if (response.game) {
					sayso.starbar.game = response.game;
				}

				sayso.log(starbar.label + ' App', starbar);

				sayso.starbar.id = starbar.id;
				sayso.starbar.shortName = starbar.short_name;
				sayso.starbar.authKey = starbar.auth_key;

				// sayso.flags can be used anywhere via sayso.flags.match('<flag_name>')
				// see starbar table, flags column
				if (starbar.flags) {
					sayso.flags = starbar.flags;
				} else {
					sayso.flags = 'none';
				}

				if (!starbar._html.length) return; // for some reason, no markup was returned

				// starbar display conditions

				if (window.opener && $SQ(window).width() < 720) { // probably a popup..

					var whiteList = ['facebook.com/pages/SaySo'], // always OK
						popup = true;

					for (var i = 0; i < whiteList.length; i++) {
						if (currentUrl.match(urlMatchPrepend + whiteList[i])) {
							popup = false;
							break;
						}
					}
					if (popup) {
						// do not load starbar for this page
						sayso.log('Popup detected');
						return;
					}

				}

				var blackList = [ // never OK
					'facebook.com/dialog', 'facebook.com/plugins', 'facebook.com/login', 'twitter.com/intent', 'twitter.com/widgets',
					'stumbleupon.com/badge', 'reddit.com/static', 'static.addtoany.com/menu',
					'plusone.google.com', 'intensedebate/empty',
					'thesaurus.com', 'reference.com', 'dictionary.com',
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

				new $SQ.jsLoadTimer().start(function () { return bi === blackList.length; }, function () {

					// ===========================================
					// Begin handling the visible console

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

					// load the specific CSS for this Starbar
					var cssStarbar = document.createElement('link');
					cssStarbar.rel = 'stylesheet';
					cssStarbar.href = starbar._css_url;
					starbarContainer.appendChild(cssStarbar);

					// append the HTML to the DOM
					var customCssLoadTimer = new cssLoadTimer();
					customCssLoadTimer.start('_sayso_starbar_css_loaded', function () {


						var jQueryLibraryLoadTimer = new $SQ.jsLoadTimer();
						jQueryLibraryLoadTimer.start('window.jQueryUILoaded', function () {

							// finally, inject the HTML!
							$SQ('#sayso-starbar').append(starbar._html);

							// load Starbar Javascript (which depends on the above data settings)
							var jsStarbar = document.createElement('script');
							jsStarbar.src = '//' + sayso.baseDomain + '/js/starbar/starbar-new.js';
							starbarContainer.appendChild(jsStarbar);

							var starbarJsTimer = new $SQ.jsLoadTimer();
							starbarJsTimer.start('window.sayso.starbar.loaded', function () {
								// if user has not "onboarded" and we are on the Starbar's base domain
								// then trigger the onboarding to display
								if (!starbar._user_map.onboarded &&
									(
										currentUrl.match(urlMatchPrepend + starbar.domain) ||
										currentUrl.match(urlMatchPrepend + 'saysollc.com') ||  // also trigger on our domains for testing purposes
										currentUrl.match(urlMatchPrepend + 'say.so')
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
