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
		if( !firstRunDone ) {
			var sTabs = safari.application.activeBrowserWindow.tabs;
			for( var i = 0; i < sTabs.length; i++ ) {
				if( sTabs[i].url.match('sayso-installing') ) {
					sTabs[i].url = chopURL(sTabs[i].url);
					sTabs[i].activate();
					return;
				}
			}
			openFirstRunTab();
		}
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
					sayso.scripts[scriptName] = content + "\n//@ sourceURL=" + url;
					callback( sayso.scripts[scriptName] );
				}
				, showErr
			);
	} else
		callback( sayso.scripts[scriptName] );
}
function getState( data, callback ) {
	if( sayso.state )
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
			currentStarbar : response.data.starbar_id,
			user : {
				id : response.data.user_id,
				key : response.data.user_key
			},
			starbarVisibility : response.data.visibility,
			profileTS : response.data.last_update_profile,
			gameTS : response.data.last_update_game,
			studies : response.data.studies,
			studiesTS : response.data.last_update_studies,
			adTargets : {},
			starbars : {},
			economies : {}
		};
		forge.prefs.set('userKey', sayso.state.user.key);
		getStarbar( sayso.state.currentStarbar, answerPendingRequests );
	}
}
function answerPendingRequests() {
	for( var i = 0; i < sayso.pendingStateRequests.length; i++ )
		sayso.pendingStateRequests[i](sayso.state);
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
		
	options.url = 'http:' + options.url;
	options.error = showErr;
	var success = options.success;
	if( success )
		options.success = function( response ) { success(response); };
	
	return forge.request.ajax(options);
}
function updateGame( content ) {
	if( content ) {
		sayso.state.economies[sayso.state.starbars[sayso.state.currentStarbar].economyId].game = content;
		forge.message.broadcast( 'update-game', content );
	} else
		ajaxWithAuth({
			url : '//'+sayso.baseDomain+'/api/gaming/get-game',
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
			url: '//'+sayso.baseDomain+'/api/user-state/update',
			data: {
				'visibility': sayso.state.starbarVisibility,
				'last_update_profile': sayso.state.profileTS,
				'last_update_game': sayso.state.gameTS
			}
	});
}
sayso.switchStarbar = function( starbarId ) {
	if( sayso.state.currentStarbar == starbarId )
		return;
	sayso.state.currentStarbar = starbarId;
	saveState();
	if( sayso.state.starbars[starbarId] )
		broadcastSwitch();
	else
		getStarbar( starbarId, broadcastSwitch );
	function broadcastSwitch() {
		forge.message.broadcast( 'starbar-switch', sayso.state );
	}
}
sayso.scripts = {};
sayso.pendingStateRequests = [];
forge.message.listen("get-state", getState, showErr);
forge.message.listen("update-game", updateGame, showErr);
forge.message.listen("set-visibility", setVisibility, showErr);
forge.message.listen("starbar-switch", sayso.switchStarbar, showErr);
forge.message.listen("get-script", getScript, showErr);
forge.logging.info("Background script loaded");
forge.prefs.get('firstRunDone', firstRun, showErr);
forge.prefs.get('userKey', getInitialState, showErr);
//@ sourceURL=sayso-background.js
