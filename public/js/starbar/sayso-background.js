function showErr( errObject ) {
	forge.logging.error( errObject.message );
}
function chopURL( url ) {
	var i ;
	if( (i = url.indexOf('?')) >= 0 )
		return url.slice(0, i);
	else if( (i = url.indexOf('#')) >= 0 )
		return url.slice(0, i);
	else
		return url;
}
function openFirstRunTab() {
	forge.tabs.open( 'http://' + sayso.baseDomain + '/starbar/install/post-install');
}
function firstRun( firstRunDone ) {
	if( !firstRunDone )
		forge.prefs.set( 'firstRunDone', true );

	if( forge.is.chrome() ) {
		if( !firstRunDone ) {
			chrome.tabs.query( {}, function( sTabs ) {
				for( var i = 0; i < sTabs.length; i++ ) {
					if( sTabs[i].url.match('sayso-installing') ) {
						chrome.tabs.update(sTabs[i].id, {url: chopURL(sTabs[i].url), active: true});
						return;
					}
				}
				openFirstRunTab();
			});
		}
	} else if( forge.is.safari() ) {
		var sTabs = safari.application.activeBrowserWindow.tabs;
		for( var i = 0; i < sTabs.length; i++ ) {
			if( sTabs[i].url.match('sayso-installing') ) {
				sTabs[i].url = chopURL(sTabs[i].url);
				sTabs[i].activate();
				return;
			}
		}
		if( !firstRunDone )
			openFirstRunTab();
	} else if( forge.is.firefox() ) {
		var code = '';
		code += "var tabs = require('tabs'); \n";
		code += "false; \n";
		code += "for( var i = 0; i < tabs.length; i++ ) \n";
		code += "	if( tabs[i].url.match('sayso-installing') ) { \n";
		code += "		var j; \n";
		code += "		if( (j = tabs[i].url.indexOf('?')) >= 0 ) \n";
		code += "			tabs[i].url = tabs[i].url.slice(0, j); \n";
		code += "		else if( (j = tabs[i].url.indexOf('#')) >= 0 ) \n";
		code += "			tabs[i].url = tabs[i].url.slice(0, j); \n";
		code += "		tabs[i].activate(); \n";
		code += "		true; \n";
		code += "		break; \n";
		code += "	} \n";
		forge.firefox.evaluate( code, function( tabFound ) {
			if( !tabFound && !firstRunDone )
				openFirstRunTab();
		} );
	}
}
function getScript( scriptName, callback ) {
	if( !sayso.scripts[scriptName] ) {
		var url = "http://" + sayso.baseDomain + "/js/" + scriptName;
		forge.request.get( url + "?_=" + ( new Date() ).getTime()
				, function(content) {
					sayso.scripts[scriptName] = content + (forge.is.ie() ? "" : "\n//@ sourceURL=" + url);
					callback( sayso.scripts[scriptName] );
				}
				, showErr
			);
	} else
		callback( sayso.scripts[scriptName] );
}
function getState( data, callback ) {
	if( sayso.state && sayso.state.starbars[sayso.state.currentStarbar] )
		callback( sayso.state );
	else
		sayso.pendingStateRequests.push( callback );
}
function getInitialState( userKey ) {
	var ajaxData = { renderer: 'json' };
	if( userKey )
		ajaxData.user_key = userKey;
	forge.request.ajax({
		dataType: 'json',
		url: 'http://' + sayso.baseDomain + '/api/user-state/get',
		data: ajaxData,
		success: gotInitialState,
		error: showErr
	});
}
function gotInitialState( response ) {
	if (response.status && response.status == "error") {
		forge.logging.warning(["Error getting user-state: ", response.data]);
	} else {
		sayso.flags = response.data.flags;
		sayso.state = {
			cacheDefeatTS : ( new Date() ).getTime(),
			currentStarbar : response.data.starbar_id,
			user : {
				id : response.data.user_id,
				key : response.data.user_key
			},
			starbarVisibility : response.data.visibility,
			profileTS : response.data.last_update_profile,
			gameTS : response.data.last_update_game,
			studies : response.data.studies,
			studiesTS : new Date(),
			notifications : response.data.notifications,
			notificationsTS : new Date(),
			missionAvailable : response.data.mission_available,
			adTargets : {},
			starbars : {},
			starbarList : response.data.starbar_list,
			economies : {}
		};
		setIntervals(response.intervals);
		forge.prefs.set('userKey', sayso.state.user.key);
		getUserData( function() {
			getStarbar( sayso.state.currentStarbar, answerPendingRequests );
		} );
	}
}
function answerPendingRequests() {
	processNotifications(sayso.state.notifications.items, []);
	for( var i = 0; i < sayso.pendingStateRequests.length; i++ )
		sayso.pendingStateRequests[i](sayso.state);
	checkForNotifications();
}
function getUserData( callback ) {
	ajaxWithAuth({
		url : 'api/user/get',
		success : function( response ) {
			sayso.state.user.data = response.data;
			if( callback )
				callback();
		}
	});
}
function updateProfile() {
	getUserData( function() {
		forge.message.broadcast( 'update-profile', sayso.state.user.data );
	});
}
function getStarbar( starbarId, callback ) {
	var params = {
		renderer : 'json',
		starbar_id : starbarId,
		user_id : sayso.state.user.id,
		user_key : sayso.state.user.key
	};

	forge.request.ajax({
		dataType: 'json',
		data : params,
		url : 'http://' + sayso.baseDomain + '/starbar/remote',
		success : gotStarbar,
		error: showErr
	});
	function gotStarbar(response) {
		if (response.status && response.status == "error") {
			forge.logging.warning(["Error getting starbar: ", response.data]);
		} else {
			var starbar = {
				id: response.data.id,
				domain: response.data.domain,
				economyId: response.data._economy.id,
				shortName: response.data.short_name,
				onboarded: response.data._user_map.onboarded,
				html: response.data._html,
				css: response.data._css_url,
				loaded: false
			}
			sayso.state.economies[starbar.economyId] = response.data._economy;
			sayso.state.economies[starbar.economyId].game = response.game;
			sayso.state.starbars[starbar.id] = starbar;
			callback();
		}
	}
}
function ajaxWithAuth(options) {
	options.data = options.data || {};
	options.data.renderer = 'json';
	options.data.starbar_id = sayso.state.currentStarbar;
	options.data.user_id = sayso.state.user.id;
	options.data.user_key = sayso.state.user.key;

	options.dataType = 'json';

	options.url = 'http://' + sayso.baseDomain + '/' + options.url;
	options.error = options.error || showErr;
	var success = options.success;
	
	options.success = function( response ) { 
		setIntervals( response.intervals );
		if( success )
			success(response);
	};

	return forge.request.ajax(options);
}
function setIntervals( intervals ) {
	if( !intervals )
		return;
	if( intervals.notifications )
		sayso.state.intervalNotifications = intervals.notifications;
	if( intervals.studies )
		sayso.state.intervalStudies = intervals.studies;
}
var gameReader = {
	getLevel: function getLevel(game) {
		return game._gamer ? game._gamer.current_level.ordinal : game.level;
	},
	getLevelName: function getLevelName(game) {
		return game._gamer ? _gamer.current_level.description : game.levels[game.level].description;
	}
}
function updateGame( content ) {
	if( content ) {
		var previousLevel = gameReader.getLevel(sayso.state.economies[sayso.state.starbars[sayso.state.currentStarbar].economyId].game);
		sayso.state.economies[sayso.state.starbars[sayso.state.currentStarbar].economyId].game = content;
		forge.message.broadcast( 'update-game', content );
		if( previousLevel != gameReader.getLevel(content) )
			getNotificationsFromServer();
	} else
		ajaxWithAuth({
			url : 'api/gaming/get-game',
			success : function(response) {
				if( response.data )
					updateGame( response.data );
			}
		});
}
function setVisibility( visibility ) {
	if( sayso.state.starbarVisibility != visibility ) {
		sayso.state.starbarVisibility = visibility;
		saveState();
		forge.message.broadcast('set-visibility', visibility);
	}
}
function saveState() {
	ajaxWithAuth( {
			url: 'api/user-state/update',
			data: {
				'visibility': sayso.state.starbarVisibility,
				'last_update_profile': sayso.state.profileTS,
				'last_update_game': sayso.state.gameTS
			}
	});
}
function addAdTarget( adTarget ) {
	sayso.state.adTargets[adTarget.type + adTarget.typeId] = adTarget;
}
function deleteAdTargets( studyAdIdArray ) {
	for (var key in sayso.state.adTargets) {
		for (var i = 0; i < studyAdIdArray.length; i++) {
			if (sayso.state.adTargets[key].id == studyAdIdArray[i]) {
				delete sayso.state.adTargets[key];
			}
		}
	}
}
function onboardingComplete() {
	ajaxWithAuth({
		url: 'api/starbar/set-onboard-status',
		data: {
			status : 1 // complete
		}
	});
	sayso.state.starbars[sayso.state.currentStarbar].onboarded = true;
}
sayso.switchStarbar = function( starbarId ) {
	function broadcastSwitch() {
		sayso.state.notifications.items = [];
		forge.message.broadcast( 'starbar-switch', sayso.state );
		getNotificationsFromServer();
	}
	if( sayso.state.currentStarbar == starbarId )
		return;
	if( sayso.state.starbarList[starbarId].active ) {
		sayso.state.currentStarbar = starbarId;
		saveState();
		if( sayso.state.starbars[starbarId] )
			broadcastSwitch();
		else
			getStarbar( starbarId, broadcastSwitch );
	} else
		ajaxWithAuth({
			url: 'api/starbar/add',
			data: { new_starbar_id: starbarId },
			success: function( response ) {
				if( response.status == 'success') {
					sayso.state.starbarList[starbarId].active = true;
					sayso.switchStarbar( starbarId );
				}
			}
		});
}
function getStudies( ignored, callback ) {
	if( !sayso.studiesReq && (new Date()) - sayso.state.studiesTS > sayso.state.intervalStudies * 1000 ) {
		sayso.studiesReq = true;
		ajaxWithAuth({
			url: 'api/study/get-all',
			success: function( response ) {
				sayso.studiesReq = false;
				if( response.status == 'success') {
					sayso.state.studies = response.data;
					sayso.state.studiesTS = new Date();
				}
			},
			error: function(errObject) {
				sayso.studiesReq = false;
				showErr(errObject);
			}
		});
	}
	callback( sayso.state.studies );
}
function checkForNotifications() {
	var remainingTime =  sayso.state.notificationsTS - (new Date()) + sayso.state.intervalNotifications * 1000;
	if( remainingTime < 0 ) {
		getNotificationsFromServer();
		remainingTime = sayso.state.intervalNotifications * 1000;
	}
	setTimeout(checkForNotifications, remainingTime + 100);
}
function getNotificationsFromServer() {
	ajaxWithAuth( {
		url: 'api/notification/get-all',
		data: { starbar_stowed: sayso.state.starbarList[sayso.state.currentStarbar].starbarVisibility == 'stowed' },
		success: function(response) {
			if(response.game) {
				updateGame(response.game);
			}
			processNotifications(response.data.items);
			sayso.state.notifications = response.data;
			sayso.state.notificationsTS = new Date();
			forge.message.broadcast('set-notifications', sayso.state.notifications.items);
		}
	});
}
function processNotifications( newSet, oldSet ) {
	if(!oldSet)
		oldSet = sayso.state.notifications.items;
	var i;
	var oldKeys = {};
	for( i = 0; i < oldSet.length; i++ )
		oldKeys[oldSet[i].id] = oldSet[i];
	for( i = 0; i < newSet.length; i++ )
		if( !oldKeys[newSet[i].id] ) {
			if (newSet[i].notification_area) {
				// Update profile if we've just received a notification regarding FB or TW getting connected.
				if (newSet[i].short_name == 'FB Account Connected' || newSet[i].short_name == 'TW Account Connected') {
					updateProfile();
				} else if (newSet[i].short_name == 'Level Up') {
					newSet[i].message = gameReader.getLevelName(sayso.state.economies[sayso.state.starbars[sayso.state.currentStarbar].economyId].game);
				}
				newSet[i].html = htmlForNotification( newSet[i] );
			} else {
				// Messages with no notification area should not be shown, they are sent silently to initiate certain actions
				ajaxWithAuth({ // Mark closed, those notifications are meant to be received only once.
					url : 'api/notification/close',
					data: { message_id: newSet[i].id }
				});
				newSet.splice(i, 1);
				i--;
			}
		} else
			newSet[i].html = oldKeys[newSet[i].id].html;
}
function htmlForNotification(notification) {
	var html = '<div class="sb_starbar-alert sb_starbar-alert-'+notification.notification_area+'" id="starbar-alert-'+notification.id+'"><div class="sb_inner"><div class="sb_content sb_theme_bgAlert'+notification.color+'">';
	if (notification['popbox_to_open']) {
		html += '<a href="//'+sayso.baseDomain+'/starbar/'+sayso.state.starbarList[sayso.state.currentStarbar].short_name+'/'+notification.popbox_to_open+'" class="sb_nav_element sb_alert" rel="sb_popBox_'+notification.popbox_to_open+'">'+notification.message+'</a>'
	} else {
		html += '<a href="#" class="sb_nav_element sb_alert" rel="">'+notification.message+'</a>';
	}

	html += '</div><!-- .sb_content --></div><!-- .sb_inner --></div><!-- #sb_alert-new -->';
	return html;
}
function closeNotification( messageId ) {
	ajaxWithAuth({
		url : 'api/notification/close',
		data: { message_id: messageId }
	});
	var notifications = sayso.state.notifications.items;
	for( var i = 0; i < notifications.length; i++ )
		if( notifications[i].id == messageId ) {
			notifications.splice(i, 1);
			sayso.state.notifications.count--;
			forge.message.broadcast('set-notifications', sayso.state.notifications.items);
		}
}
function missionComplete() {
	sayso.state.missionAvailable = false;
	forge.message.broadcast('set-mission-available', false);
	getNotificationsFromServer();
}
function brandBoostEvent( data ) {
	sayso.brandBoostSessions = sayso.brandBoostSessions || {};
	var sessionId;
	var eventNames = { Begin: 'launch_screen', Interstitial: 'interstitial_screen', End: 'end_screen' };
	var fields = {a: 'campaign_id', pn: 'partner_name', gn: 'game_name', i: 'item_id', sponsorName: 'sponsor_name', uid: 'uid'};
	var eventData = { event_source: 'brandBoostEvent' };
	
	if(!eventNames[data.stage])
		return;
		
	eventName = 'brandboost_' + eventNames[data.stage];
	
	if( data.stage == 'Begin' ) {
		sessionId = Math.floor(Math.random()*2e9) + 1;
		sayso.brandBoostSessions[data.topFrameId] = sessionId;
	} else
		sessionId = sayso.brandBoostSessions[data.topFrameId];
		
	if( sessionId )
		eventData.brandboost_session_id = sessionId;
		
	for( var field in fields )
		if( data.urlParams[field] )
			eventData['brandboost_' + fields[field]] = data.urlParams[field];
			
	ajaxWithAuth( {
		url: 'api/metrics/event-submit',
		data: { event_name: eventName, event_data: eventData }
	});
}
function submitEvent( data ) {
    if( !(data instanceof Object) )
        data = JSON.parse(JSON.stringify(data));
	ajaxWithAuth( {
		url: 'api/metrics/event-submit',
		data: data
	});
}
sayso.scripts = {};
sayso.pendingStateRequests = [];
forge.message.listen("get-state", getState, showErr);
forge.message.listen("update-game", updateGame, showErr);
forge.message.listen("update-profile", updateProfile, showErr);
forge.message.listen("update-notifications", getNotificationsFromServer, showErr);
forge.message.listen("set-visibility", setVisibility, showErr);
forge.message.listen("starbar-switch", sayso.switchStarbar, showErr);
forge.message.listen("add-ad-target", addAdTarget, showErr);
forge.message.listen("delete-ad-targets", deleteAdTargets, showErr);
forge.message.listen("onboarding-complete", onboardingComplete, showErr);
forge.message.listen("get-studies", getStudies, showErr);
forge.message.listen("close-notification", closeNotification, showErr);
forge.message.listen("mission-complete", missionComplete, showErr);
forge.message.listen("brandboost-event", brandBoostEvent, showErr);
forge.message.listen("submit-event", submitEvent, showErr);
forge.message.listen("get-script", getScript, showErr);
forge.logging.info("Background script loaded");
forge.prefs.get('firstRunDone', firstRun, showErr);
forge.prefs.get('userKey', getInitialState, showErr);
//@ sourceURL=sayso-background.js
