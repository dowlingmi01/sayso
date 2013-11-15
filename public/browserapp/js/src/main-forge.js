(function(global, $, forge, state, browserapp, config, dommsg, util, commrelay, track, comm, api) {
	var in_iframe = forge.is.firefox() ? (global.unsafeWindow.window !== global.unsafeWindow.top) : (global.top !== global);
	var frameId = Math.floor(Math.random()*2e9) + 1;
	var url_match_prepend = '^(?:http|https){1}://(?:[\\w.-]+[.])?';
	var webportal = false;

	function injectBeacon() {
		window.$SaySoExtension = { commRelay: true };
		$SaySoExtension.ssBeacon = function ssBeacon( ssData ) {
			window.postMessage(JSON.stringify(['sayso-beacon', ssData]), '*');
		};
	}

	function handleBeacon( ssData ) {
		if( typeof ssData === 'string' ) {
			try {
				ssData = JSON.parse( ssData );
			} catch( e ) {
				util.log('Invalid Beacon', ssData);
				return;
			}
		}
		if( !ssData.event_name ) {
			util.log('Invalid Beacon', ssData);
			return;
		}
		util.log( 'Received Beacon data: ', ssData );
		var eventName = ssData.event_name;
		delete ssData.event_name;
		ssData.event_source = 'handleBeacon';
		comm.request('submit-event', { event_name: eventName, event_data: ssData });
	}

	function shouldLoadStarbar() {
		if( state.state.starbar.id != 4 && state.state.starbar.id != 7 )
			return false;

		if( webportal || in_iframe )
			return false;

		if( global.opener && $(global).width() < 720) { // probably a popup.

			var whiteList = ['facebook.com/pages/SaySo'], // always OK
				popup = true;

			for( var i = 0; i < whiteList.length; i++ ) {
				if (config.location.href.match(url_match_prepend + whiteList[i])) {
					popup = false;
					break;
				}
			}
			if( popup ) {
				// do not load starbar for this page
				util.log('Popup detected');
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

		for (var bi = 0; bi < blackList.length; bi++) {
			if (config.location.href.match(url_match_prepend + blackList[bi])) {
				// do not load starbar for this page
				util.log('Blacklisted: ' + blackList[bi] + ' - Not loading Starbar');
				return false;
			}
		}

		if( $('embed[type*="pdf"]').length > 0 )
			return false; // Don't load on Google Docs

		return true;
	}
	function fixFlashElements() {
		// fix <embed> FLASH elements!
		$('embed[src*=".swf"], embed[type*="flash"]').not('.saysofixed').each(function() {
			var $SQembed = $(this);

			if ($SQembed.attr('id') === 'sm2movie') return true; // no fix needed, go to next <embed>

			$SQembed.css('z-index', '9998 !important');
			if ($SQembed.attr('wmode') !== 'transparent' && $SQembed.attr('wmode') !== 'opaque') {
				$SQembed.attr('wmode', 'transparent');
				var newElem = $SQembed.clone(true, true);
				newElem.addClass('saysofixed');
				$SQembed.replaceWith(newElem);
			}
		});

		// fix <object> FLASH elements!
		$('object').not('.saysofixed').each(function() {
			var $SQobject = $(this);
			if ($SQobject.attr('id') === 'sm2movie' || $SQobject.attr('id') === 'FS') return true; // no fix needed, go to next <object>
			var $SQwmodeParam = $('param[name="wmode"]', $SQobject);
			if ($SQwmodeParam.length === 1) {
				if ($SQwmodeParam.attr('value') === 'transparent' || $SQwmodeParam.attr('value') === 'opaque') {
					return true; // no fix needed, go to next <object>
				} else {
					$SQwmodeParam.attr('value', 'transparent');
				}
			} else {
				// Check if this <object> is flash, if so add the wmode parameter
				var $SQmovieParam = $('param[name="movie"]', $SQobject);
				var $SQobjectType = $SQobject.attr('type');
				if (($SQmovieParam.length === 1 && $SQmovieParam.attr('value').match(/.swf/)) || ($SQobjectType && $SQobjectType.match(/flash/))) {
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
			if (elemBeforeObject.length !== 0) {
				newElem.insertAfter(elemBeforeObject);
			} else {
				container.prepend(newElem);
			}
		});
	}
	function whenStateReady( callback ) {
		if( state.ready )
			callback();
		else
			$(global.document).on('sayso:state-ready', callback);
	}
	function loadStarbarIfNeeded() {
		if( shouldLoadStarbar() )
			$(function(){
				fixFlashElements();
				var timeSpentFixingFlashSoFar = 0;
				var timeBetweenFlashFixes = 500;
				var maximumTimeToWaitForFlashToLoad = 5000; // 5 seconds
				$.doTimeout('flashFixer', timeBetweenFlashFixes, function () {
					if (timeSpentFixingFlashSoFar > maximumTimeToWaitForFlashToLoad) {
						return false; // stop the loop
					}
					timeSpentFixingFlashSoFar += timeBetweenFlashFixes;
					fixFlashElements();
					return true; // keep doTimeout schedule going
				});
				$(global.document).on('sayso:state-login sayso:state-logout sayso:state-starbar', browserapp.initApp);
				browserapp.initApp();
			});
	}

	if( in_iframe && config.location.href.match(/saysollc.com\/browserapp\/readStorage.html/))
		return;

	track(in_iframe, frameId);

	if( (!in_iframe || config.location.pathname.match(/\/machinimareload.html/)) &&
		config.location.host.match(/say.so|saysollc.com/) ) {
		webportal = true;
		commrelay.install();
	}

	dommsg.addHandler('beacon', handleBeacon);
	util.evalInPageContext(injectBeacon);

	if( !in_iframe ) {
		var missionShortName = config.location.href.match(/(?:.say.so|.saysollc.com\/.*)\/mission\/(.*)\//);
		if( missionShortName ) {
			missionShortName = missionShortName[1];
			function handleMissionProgress( data ) {
				api.doRequest( {
					action_class: 'Survey',
					action: 'updateMissionProgress',
					starbar_id: state.state.starbar.id,
					top_frame_id: frameId,
					mission_short_name: missionShortName,
					mission_data: data
				}, function() {
					if( data.stage === data.data.stages.length )
						forge.message.broadcastBackground('mission-complete');
				});
			}
			dommsg.addHandler('mission-progress', handleMissionProgress);
			return; // DO NOT LOAD STARBAR
		}
	}

	whenStateReady(loadStarbarIfNeeded);

}(this, jQuery, forge, sayso.module.state, sayso.module.browserapp, sayso.module.config, sayso.module.dommsg,
		sayso.module.util, sayso.module.commrelay, sayso.module.track, sayso.module.comm, sayso.module.api))
;
