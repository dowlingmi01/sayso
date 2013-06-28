(function(global, $, forge, state, browserapp, config, dommsg, util, commrelay) {
	var in_iframe = forge.is.firefox() ? (global.unsafeWindow.window !== global.unsafeWindow.top) : (global.top !== global);
	var frameId = Math.floor(Math.random()*2e9) + 1;
	var url_match_prepend = '^(?:http|https){1}://(?:[\\w.-]+[.])?';
	var parentLocation, topFrameId;
	var webportal = false;

	function evalInPageContext( arg ) {
		var scriptEl = document.createElement('script');
		if( typeof arg === "function" )
			scriptEl.text = '(' + arg + ')();';
		else
			scriptEl.text = arg;
		document.head.appendChild(scriptEl);
		document.head.removeChild(scriptEl);
	}

	function injectBeacon() {
		window.$SaySoExtension = {};
		$SaySoExtension.ssBeacon = function ssBeacon( ssData ) {
			window.postMessage(JSON.stringify(['sayso-beacon', ssData]), '*');
		};
	}

	function handleBeacon( ssData ) {
		if( typeof ssData === 'string' ) {
			try {
				ssData = JSON.parse( ssData );
			} catch( e ) {
				global.console.log('Invalid Beacon', ssData);
				return;
			}
		}
		if( !ssData.event_name ) {
			global.console.log('Invalid Beacon', ssData);
			return;
		}
		global.console.log( 'Received Beacon data: ', ssData );
		var eventName = ssData.event_name;
		delete ssData.event_name;
		ssData.event_source = 'handleBeacon';
		forge.message.broadcastBackground('submit-event', { event_name: eventName, event_data: ssData });
	}

	function shouldLoadStarbar() {
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
				global.console.log('Popup detected');
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
				global.console.log('Blacklisted: ' + blackList[bi] + ' - Not loading Starbar');
				return false;
			}
		}

		if( $('embed[type*="pdf"]').length > 0 )
			return false; // Don't load on Google Docs

		return true;
	}
	if( !in_iframe && config.location.href.match(/say.so|saysollc.com\/webportal/) ) {
		webportal = true;
		commrelay.install();
	}

	dommsg.addHandler('beacon', handleBeacon);
	evalInPageContext(injectBeacon);
	if( !in_iframe ) {
		topFrameId = frameId;
		function handleParentReq( childFrameId ) {
			forge.message.broadcast('parent-location-' + childFrameId,
				{ location: config.location, frameId: frameId });
		}
		dommsg.addHandler('parent-req', handleParentReq);
	} else {
		forge.message.listen('parent-location-' + frameId, function( m ) {
			if( !parentLocation ) {
				parentLocation = m.location;
				topFrameId = m.frameId;
				//sayso.fn.loadScript('starbar/sayso.js');
				if( config.location.host === 'vex.wildtangent.com') {
					var brandBoostStage = config.location.pathname.match(/\/(?:Vex\/)?(\w+)(?:.aspx)?/);
					if( brandBoostStage ) {
						var par = util.urlParams(config.location.search.substring(1));
						forge.message.broadcastBackground( 'brandboost-event', { stage: brandBoostStage[1], urlParams: par, topFrameId: topFrameId } );
					}
				}
				/*
				if( config.location.href.match(/:\/\/simssoc.game.playfish.com\/g\/fb\/simssoc\//) ) {
					var elementFound = false;
					function monitorElement() {
						if( $('div#overlay div#bank').length ) {
							if( !elementFound ) {
								elementFound = true;
								forge.message.broadcastBackground('submit-event', { event_name: 'add_cash', event_data: { event_source: 'monitorElement', game_name: 'simssocial', game_source: 'facebook', add_cash_stage: 'open'} } );
							}
						} else if( elementFound )
							elementFound = false;
						setTimeout( monitorElement, 1000 );
					}
					monitorElement();
				}
				*/
			}
		});
		function requestParentLocation() {
			if( !parentLocation ) {
				evalInPageContext( "top.postMessage( '[\"sayso-parent-req\", " + frameId + "]', '*' );");
				setTimeout(requestParentLocation, 200);
			}
		}
		requestParentLocation();
	}
	if( shouldLoadStarbar() )
		$(function(){
			$(global.document).on('sayso:state-login sayso:state-logout sayso:state-ready sayso:state-starbar', browserapp.initApp);
			if( state.ready )
				browserapp.initApp();
		});
}(this, jQuery, forge, sayso.module.state, sayso.module.browserapp, sayso.module.config, sayso.module.dommsg,
		sayso.module.util, sayso.module.commrelay))
;
