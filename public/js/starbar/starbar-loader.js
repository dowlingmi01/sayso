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

	// Insert the starbar container
 	if ($SQ('#sayso-starbar').length < 1)
 		$SQ('body').append('<div id="sayso-starbar" style="position: fixed; left: 0px; bottom: 0px; width: 100%; background: none; margin-bottom: -3px; z-index: 9999;"></div><div id="sayso-frame-comm" style="display:none;"></div>');

	var starbarContainer = document.getElementById('sayso-starbar')

	$SQ.ajaxWithAuth = ajaxWithAuth;
	$SQ.cssLoadTimer = cssLoadTimer;
	$SQ.insertCommunicationIframe = insertCommunicationIframe;

	// App loading

	/* Feature checks */
	var ieVersion = sayso.ie_version ;

	if (ieVersion > -1 && ieVersion < 9) {
		sayso.disableJqueryEffects = true;
		$SQ.fx.off = true;
	} else {
		sayso.disableJqueryEffects = false;
	}

	// test if HTML5 placeholder is supported or not
	if (ieVersion > -1) {
		sayso.placeholderSupportMissing = true;
	} else {
		sayso.placeholderSupportMissing = false;
	}

	// load JS dependencies
	var jsDep = [ 'jquery-ui-1.8.16.custom.min.js'
				, 'jquery.jscrollpane.min.js'
				, 'jquery.cookie.js'
				, 'jquery.jeip.js'
				, 'jquery.easyTooltip.js'
				, 'jquery.cycle.all.js'
				, 'sayso-shared.js'
				, 'starbar-new.js'
				];

	for( var jsi = 0; jsi < jsDep.length; jsi++ )
		window.sayso.loadScript('starbar/'+jsDep[jsi]);


	fixFlashElements();
	loadStarbar();

	timeSpentFixingFlashSoFar = 0;
	timeBetweenFlashFixes = 500;
	maximumTimeToWaitForFlashToLoad = 5000; // 5 seconds
	$SQ.doTimeout('flashFixer', timeBetweenFlashFixes, function () {
		if (timeSpentFixingFlashSoFar > maximumTimeToWaitForFlashToLoad) {
			return false; // stop the loop
		}
		timeSpentFixingFlashSoFar += timeBetweenFlashFixes;
		fixFlashElements();
		return true; // keep doTimeout schedule going
	});

	function loadStarbar () {

		// ===========================================
		// Begin handling the visible console

		// bring in the GENERIC CSS

		var cssGeneric = document.createElement('link');
		cssGeneric.rel = 'stylesheet';
		cssGeneric.href = '//' + sayso.baseDomain + '/css/starbar-generic.css';
		starbarContainer.appendChild(cssGeneric);

		// load the specific CSS for this Starbar
		var cssStarbar = document.createElement('link');
		cssStarbar.rel = 'stylesheet';
		cssStarbar.href = sayso.starbar.css;
		starbarContainer.appendChild(cssStarbar);

		// append the HTML to the DOM
		var customCssLoadTimer = new cssLoadTimer();
		customCssLoadTimer.start('_sayso_starbar_css_loaded', function () {


			var jQueryLibraryLoadTimer = new $SQ.jsLoadTimer();
			jQueryLibraryLoadTimer.start('window.jQueryUILoaded', function () {

				// finally, inject the HTML!
				$SQ('#sayso-starbar').append(sayso.starbar.html);


				var starbarJsTimer = new $SQ.jsLoadTimer();
				starbarJsTimer.start('window.sayso.starbar.loaded', function () {
					// initialize the starbar
					sayso.initStarBar();

					// if user has not "onboarded" and we are on the Starbar's base domain
					// then trigger the onboarding to display
					if (!sayso.starbar.onboarded &&
						(
							sayso.current_url.match(sayso.url_match_prepend + sayso.starbar.domain) ||
							sayso.current_url.match(sayso.url_match_prepend + 'saysollc.com') ||  // also trigger on our domains for testing purposes
							sayso.current_url.match(sayso.url_match_prepend + 'say.so')
						)
					) {
						// trigger onboarding to display (see starbar-new.js where this is handled)
						setTimeout(function () { $SQ(document).trigger('onboarding-display'); }, 2000);
						// bind when the last step of the onboarding is selected, to mark onboarding done
						// see starbar-new.js where this is triggered
					}
				});
			});
		}); // end CSS load timer
	} // end loadStarbar()

	forge.message.listen( 'starbar-switch', function( response ) {
		sayso.state = response;
		sayso.starbar = response.starbars[response.currentStarbar];
		sayso.starbar.user = response.user;
		sayso.starbar.state = {visibility: sayso.state.starbarVisibility, profile: sayso.state.profileTS, game: sayso.state.gameTS};
		sayso.starbar.game = sayso.state.economies[sayso.starbar.economyId].game;

		sayso.starbar.economy = sayso.state.economies[sayso.starbar.economyId];

		sayso.starbar.loaded = true;
		$SQ('#sayso-starbar').html('');
		loadStarbar();
	});

	function ajaxWithAuth(options) {
		options.data = $SQ.extend(options.data || {}, {
			renderer : 'json',
			starbar_id : sayso.starbar.id,
			user_id : sayso.starbar.user.id,
			user_key : sayso.starbar.user.key
		});

		if (!options.dataType)
			options.dataType = 'json';

		if( !options.frame_id && sayso.frameId ) {
			options.frame_id = sayso.frameId;
			options.xdm_c = sayso.frameId;
		}

		options.beforeSend = function(x) {
			if (x && x.overrideMimeType) {
				x.overrideMimeType("application/j-son;charset=UTF-8");
			}
		};
		options.url = 'http:' + options.url;
		return forge.request.ajax(options);
	}

	function insertCommunicationIframe(link, container, width, height, scrolling) {
		// This function inserts the iframe (with x-domain communication enabled!)
		// The id of the container is placed inside the 'ref' attribute at the top of the accordion

		if (link.indexOf("?") == -1) link += "?";
		else link += "&";

		var ifr = $SQ(document.createElement("iframe")).attr({src: link+"frame_id="+$SQ.sayso.frameId+"&xdm_c="+$SQ.sayso.frameId, scrolling: scrolling}).css({
			height: parseInt(height)+"px",
			width: parseInt(width)+"px",
			margin: 0,
			border: 0
		});
		$SQ('#'+container).append(ifr);
		$SQ.sayso.starbar.openFrameContainer = $SQ('#'+container);
		$SQ.sayso.starbar.openFrame = $SQ.sayso.starbar.openFrameContainer.children('iframe');
	}

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

	function fixFlashElements () {
		// fix <embed> FLASH elements!
		$SQ('embed[src*=".swf"], embed[type*="flash"]').not('.saysofixed').each(function(index) {
			var $SQembed = $SQ(this);

			if ($SQembed.attr('id') == 'sm2movie') return true; // no fix needed, go to next <embed>

			$SQembed.css('z-index', '9998 !important');
			if ($SQembed.attr('wmode') != 'transparent' && $SQembed.attr('wmode') != 'opaque') {
				$SQembed.attr('wmode', 'transparent');
				var newElem = $SQembed.clone(true, true);
				newElem.addClass('saysofixed');
				$SQembed.replaceWith(newElem);
			}
		});

		// fix <object> FLASH elements!
		$SQ('object').not('.saysofixed').each(function(index) {
			var $SQobject = $SQ(this);
			if ($SQobject.attr('id') == 'sm2movie' || $SQobject.attr('id') == 'FS') return true; // no fix needed, go to next <object>
			var $SQwmodeParam = $SQ('param[name="wmode"]', $SQobject);
			if ($SQwmodeParam.length == 1) {
				if ($SQwmodeParam.attr('value') == 'transparent' || $SQwmodeParam.attr('value') == 'opaque') {
					return true; // no fix needed, go to next <object>
				} else {
					$SQwmodeParam.attr('value', 'transparent');
				}
			} else {
				// Check if this <object> is flash, if so add the wmode parameter
				var $SQmovieParam = $SQ('param[name="movie"]', $SQobject);
				var $SQobjectType = $SQobject.attr('type');
				if (($SQmovieParam.length == 1 && $SQmovieParam.attr('value').match(/.swf/)) || ($SQobjectType && $SQobjectType.match(/flash/))) {
					var newParam = document.createElement('param');
					newParam.setAttribute('name', 'wmode');
					newParam.setAttribute('value', 'transparent');
					$SQobject.append(newParam);
				} else {
					return true; // not flash, go to next <object>
				}
			}
			$SQobject.css('z-index', '9998 !important');

			var container = $SQobject.parent();
			var newElem = $SQobject.clone(true);
			newElem.addClass('saysofixed');
			var elemBeforeObject = $SQobject.prev();
			$SQobject.remove();
			if (elemBeforeObject.length != 0) {
				newElem.insertAfter(elemBeforeObject);
			} else {
				container.prepend(newElem);
			}
		});
	}
})();
