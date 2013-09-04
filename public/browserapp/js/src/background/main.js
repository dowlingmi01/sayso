(function(global, Api, comm, config, getSession){
	var state = {
		starbars: {},
		games: {},
		notifications: {},
		surveyCounts: {},
		loggedIn: null,
		session: {},
		studies: [],
		adTargets: []
	};
	var pendingStarbars = {};
	var pendingRequests = {};
	var brandBoostSessions = {};
	function getPublicApi() {
		return new Api(config.baseDomain);
	}
	function getSessionApi() {
		return new Api(config.baseDomain, state.session.id, state.session.key);
	}
	function checkForNewSession(response) {
		if( response && response.common_data &&
			response.common_data.new_session_id && response.common_data.new_session_key ) {
			state.session = { id: response.common_data.new_session_id, key: response.common_data.new_session_key,
				timestamp: (new Date()).getTime() };
			comm.set('session', state.session);
		}
	}
	function login( data, callback ) {
		getPublicApi().sendRequest( {action_class: 'Login', action: 'login', username: data.email, password: data.password}, function( data ) {
            var session = data.responses['default'].variables;
            var result = {result: false, response: {}};
			if (session) {
				session = { id: session.session_id, key: session.session_key, timestamp: (new Date()).getTime() };
				state.session = session;
				comm.set('session', session, function() {
					state.loggedIn = null;
					getUserState();
					comm.broadcast('state.login');
				});
                result.result = true;
                result.response = session;
			}
            else {
                result.response = data.responses['default'];
            }
            if( callback ) {
                callback(result);
            }
		});
	}
	function loginMachinimaReload( data ) {
		getPublicApi().sendRequest( {action_class: 'Login', action: 'machinimaReloadLogin',
			email: data.email, digest: data.digest}, function( data ) {
			var session = data.responses['default'].variables;
			if (session) {
				session = { id: session.session_id, key: session.session_key, timestamp: (new Date()).getTime() };
				state.session = session;
				comm.set('session', session, function() {
					state.loggedIn = null;
					getUserState();
				});
			} else {
				state.loggedIn = false;
				for( var starbarId in pendingRequests )
					for( var i in pendingRequests[starbarId] )
						pendingRequests[starbarId][i]( {loggedIn:false} );
				pendingRequests = {};
				pendingStarbars = {};
			}
		});
	}
	function logout(unused, callback) {
		getPublicApi().sendRequest( {action_class: 'Login', action: 'logout', current_session_id: state.session.id} );
		state.starbars = {};
		state.games = {};
		state.notifications = {};
		state.loggedIn = false;
		state.session = {};
		comm.set('session', {});
		callback();
		comm.broadcast('state.logout', {loggedIn:false});
	}
	function getUserState() {
		getSession( function( session ) {
			session = session || {};
			state.session = session;
			if( session.id && session.key ) {
				var api = getSessionApi();
				api.setRequest( 'user', {action_class: 'User', action: 'getUser'} );
				api.setRequest( 'state', {action_class: 'User', action: 'getState'} );
				if( config.extVersion )
					api.setRequest( 'studies', {action_class: 'LegacyApi', action: 'call',
						legacy_class: 'Study', legacy_action: 'getAll'} );
				api.sendRequests( function(data) {
					checkForNewSession(data);
					state.loggedIn = true;
					state.profile = data.responses.user.records[0];
					state.currentStarbarId = data.responses.state.variables.starbar_id;
					state.visibility = data.responses.state.variables.visibility;
					if( config.extVersion )
						state.studies = data.responses.studies.records;
					if( pendingRequests[0] ) {
						if( pendingRequests[state.currentStarbarId] )
							pendingRequests[state.currentStarbarId].concat(pendingRequests[0]);
						else
							pendingRequests[state.currentStarbarId] = pendingRequests[0];
						delete pendingRequests[0];
					}
					if( config.extVersion && !pendingRequests[state.currentStarbarId] )
						pendingRequests[state.currentStarbarId] = [];
					for( var starbarId in pendingRequests )
						getStarbarState( starbarId );
				} );
			} else {
				state.loggedIn = false;
				for( var starbarId in pendingRequests )
					for( var i in pendingRequests[starbarId] )
						pendingRequests[starbarId][i]( {loggedIn:false} );
				pendingRequests = {};
				pendingStarbars = {};
			}
		} );
	}
	function getStarbarState( starbarId ) {
		if( pendingStarbars[starbarId] )
			return;
		pendingStarbars[starbarId] = true;
		var api = getSessionApi();
		api.setRequest( 'game', {action_class: 'Game', action: 'getGame', starbar_id: starbarId} );
		api.setRequest( 'notifications', {action_class: 'Notification', action: 'getUserNotifications', starbar_id: starbarId} );
		api.setRequest( 'starbar', {action_class: 'Starbar', action: 'getStarbar', starbar_id: starbarId} );
		api.setRequest( 'markup', {action_class: 'Markup', action: 'getMarkup', starbar_id: starbarId, app: 'browserapp', key: 'nav'} );
		if( config.extVersion )
			api.setRequest( 'missionCount', {action_class: 'Survey', action: 'getSurveyCounts',
				starbar_id: starbarId, survey_type: 'mission', survey_status: 'new'});

		api.sendRequests( function(data) {
			checkForNewSession(data);
			if( !data.responses.starbar.errors ) {
				state.starbars[starbarId] = data.responses.starbar.records[0];
				state.starbars[starbarId].id = starbarId;
				state.starbars[starbarId].markup = data.responses.markup.variables.markup;
				state.games[state.starbars[starbarId].economy_id] = data.responses.game.variables.game;
				state.notifications[starbarId] = data.responses.notifications.records;
				if( config.extVersion )
					state.surveyCounts[starbarId] = {mission: data.responses.missionCount.variables.count};
			}
			if( pendingRequests[starbarId] ) {
				var stateForStarbar = buildStateForStarbar(starbarId);
				for( var i in pendingRequests[starbarId] )
					pendingRequests[starbarId][i](stateForStarbar);
				delete pendingRequests[starbarId];
			}
			delete pendingStarbars[starbarId];
		} );
	}
	function buildStateForStarbar( starbarId ) {
		var result;
		if( state.loggedIn ) {
			result = {
				loggedIn: state.loggedIn,
				profile: state.profile,
				visibility: state.visibility,
				studies: state.studies,
				adTargets: state.adTargets
			};
			if( state.starbars[starbarId] ) {
				result.starbar = state.starbars[starbarId];
				result.notifications = state.notifications[starbarId];
				result.surveyCounts = state.surveyCounts[starbarId];
				result.game = state.games[state.starbars[starbarId].economy_id];
			}
		} else
			result = { loggedIn: false };
		return result;
	}
	function addPendingRequest( starbarId, callback ) {
		if( !pendingRequests[starbarId] )
			pendingRequests[starbarId] = [];
		pendingRequests[starbarId].push( callback );
	}
	function getState( data, callback ) {
		var starbarId = (data && data.starbar_id) || state.currentStarbarId || 0;
		if( state.loggedIn === false && data.machinimareload ) {
			addPendingRequest( starbarId, callback );
			loginMachinimaReload(data.machinimareload);
		} else if( state.loggedIn === false || (state.loggedIn && state.starbars[starbarId]) )
			callback( buildStateForStarbar(starbarId) );
		else {
			addPendingRequest( starbarId, callback );
			if( state.loggedIn )
				getStarbarState( starbarId );
		}
	}
	function setVisibility( data ) {
		state.visibility = data;
		comm.broadcast('state.visibility', state.visibility);
		getSessionApi().sendRequest({action_class: 'User', action: 'updateState', state_data: {visibility: data}},
			checkForNewSession);
	}
	function apiDoRequests( requests, callback ) {
		if (!requests) return;
		var api = getSessionApi();
		for (var request in requests) {
			api.setRequest( request, requests[request] );
		}
		api.sendRequests( processApiResponse );
		function processApiResponse( data ) {
			callback(data);
			if( state.games && data.common_data && data.common_data.game ) {
				state.games[data.common_data.game.economy_id] = data.common_data.game;
				comm.broadcast('state.game', data.common_data.game);
			}
			if( state.profile && data.common_data && data.common_data.user && data.common_data.user.id === state.profile.id ) {
				state.profile = data.common_data.user;
				comm.broadcast('state.profile', data.common_data.user);
			}
			checkForNewSession(data);
		}
	}
	function addAdTarget( adTarget ) {
		state.adTargets[adTarget.type + adTarget.typeId] = adTarget;
	}
	function deleteAdTargets( studyAdIdArray ) {
		for (var key in state.adTargets) {
			for (var i = 0; i < studyAdIdArray.length; i++) {
				if (state.adTargets[key].id === studyAdIdArray[i]) {
					delete state.adTargets[key];
				}
			}
		}
	}
	function brandBoostEvent( data ) {
		var sessionId;
		var eventNames = { Begin: 'launch_screen', Interstitial: 'interstitial_screen', End: 'end_screen' };
		var fields = {a: 'campaign_id', pn: 'partner_name', gn: 'game_name', i: 'item_id', sponsorName: 'sponsor_name', uid: 'uid'};
		var eventData = { event_source: 'brandBoostEvent' };

		if(!eventNames[data.stage])
			return;

		var eventName = 'brandboost_' + eventNames[data.stage];

		if( data.stage === 'Begin' ) {
			sessionId = Math.floor(Math.random()*2e9) + 1;
			brandBoostSessions[data.topFrameId] = sessionId;
		} else
			sessionId = brandBoostSessions[data.topFrameId];

		if( sessionId )
			eventData.brandboost_session_id = sessionId;

		for( var field in fields )
			if( data.urlParams[field] )
				eventData['brandboost_' + fields[field]] = data.urlParams[field];
		getSessionApi().sendRequest({action_class: 'LegacyApi', action: 'call',
			legacy_class: 'Metrics', legacy_action: 'eventSubmit',
			parameters: { event_name: eventName, event_data: eventData }}, checkForNewSession);
	}
	function submitEvent( data ) {
		if( !(data instanceof Object) )
			data = JSON.parse(JSON.stringify(data));
		getSessionApi().sendRequest({action_class: 'LegacyApi', action: 'call',
			legacy_class: 'Metrics', legacy_action: 'eventSubmit',
			parameters: data}, checkForNewSession);
	}
	function missionComplete() {
		if( state.surveyCounts[state.currentStarbarId].mission > 0 ) {
			state.surveyCounts[state.currentStarbarId].mission--;
			comm.broadcast('state.surveyCounts', {starbar_id: state.currentStarbarId,
				surveyCounts: state.surveyCounts[state.currentStarbarId]});
		}
	}

	comm.listen('get-state', getState);
	comm.listen('api-do-requests', apiDoRequests);
	comm.listen('login', login);
	if( !config.extVersion )
		comm.listen('logout', logout);
	comm.listen('set-visibility', setVisibility);
	comm.listen('add-ad-target', addAdTarget);
	comm.listen('delete-ad-targets', deleteAdTargets);
	comm.listen('brandboost-event', brandBoostEvent);
	comm.listen('submit-event', submitEvent);
	comm.listen('mission-complete', missionComplete);

	getUserState();
})(this, sayso.module.Api, sayso.module.comm, sayso.module.config, sayso.module.getSession);
