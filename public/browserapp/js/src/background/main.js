(function(global, Api, comm, config, getSession){
	var baseDomain = config.baseDomain;
	var api = new Api(baseDomain);
	var state = {
		starbars: {},
		games: {},
		notifications: {},
		loggedIn: null,
		baseDomain: baseDomain
	};
	var pendingStarbars = {};
	var pendingRequests = {};
	function login( data, callback ) {
		api.sendRequest( {action_class: 'Login', action: 'login', username: data.email, password: data.password}, function( data ) {
            var session = data.responses['default'].variables;
            var result = {result: false, response: {}};
			if (session) {
				session = { id: session.session_id, key: session.session_key };
				comm.set('session', session, function() {
					state.loggedIn = null;
					getUserState();
					comm.broadcast('state.login');
				});
                result.result = true;
                result.response = session;
				api = new Api(baseDomain, session.id, session.key);
			}
            else {
                result.response = data.responses['default'];
            }
            if( callback ) {
                callback(result);
            }
		});
	}
	function logout(unused, callback) {
		api = new Api(baseDomain);
		api.sendRequest( {action_class: 'Login', action: 'logout', current_session_id: state.session.id} );
		state.starbars = {};
		state.games = {};
		state.notifications = {};
		state.loggedIn = false;
		state.session = {};
		comm.set('session', {});
		callback();
		comm.broadcast('state.logout', {loggedIn:false});

		// reset api instance to be logged out
		api = new Api(baseDomain);
	}
	function getUserState() {
		getSession( function( session ) {
			if( session && session.id && session.key ) {
				if (!api.session_id) api = new Api(baseDomain, session.id, session.key);
				api.setRequest( 'user', {action_class: 'User', action: 'getUser'} );
				api.setRequest( 'state', {action_class: 'User', action: 'getState'} );
				api.sendRequests( function(data) {
					state.loggedIn = true;
					state.session = session;
					state.profile = data.responses.user.records[0];
					state.currentStarbarId = data.responses.state.variables.starbar_id;
					state.visibility = data.responses.state.variables.visibility;
					if( pendingRequests[0] ) {
						if( pendingRequests[state.currentStarbarId] )
							pendingRequests[state.currentStarbarId].concat(pendingRequests[0]);
						else
							pendingRequests[state.currentStarbarId] = pendingRequests[0];
						delete pendingRequests[0];
					}
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
		api.setRequest( 'game', {action_class: 'Game', action: 'getGame', starbar_id: starbarId} );
		api.setRequest( 'notifications', {action_class: 'Notification', action: 'getUserNotifications', starbar_id: starbarId} );
		api.setRequest( 'starbar', {action_class: 'Starbar', action: 'getStarbar', starbar_id: starbarId} );
		api.setRequest( 'markup', {action_class: 'Markup', action: 'getMarkup', starbar_id: starbarId, app: 'browserapp', key: 'nav'} );
		api.sendRequests( function(data) {
			state.starbars[starbarId] = data.responses.starbar.records[0];
			state.starbars[starbarId].id = starbarId;
			state.starbars[starbarId].markup = data.responses.markup.variables.markup;
			state.games[state.starbars[starbarId].economy_id] = data.responses.game.variables.game;
			state.notifications[starbarId] = data.responses.notifications.records;
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
		return state.loggedIn ? {
			loggedIn: state.loggedIn,
			profile: state.profile,
			visibility: state.visibility,
			starbar: state.starbars[starbarId],
			notifications: state.notifications[starbarId],
			game: state.games[state.starbars[starbarId].economy_id]
		} : { loggedIn: false };
	}
	function addPendingRequest( starbarId, callback ) {
		if( !pendingRequests[starbarId] )
			pendingRequests[starbarId] = [];
		pendingRequests[starbarId].push( callback );
	}
	function getState( data, callback ) {
		var starbarId = (data && data.starbar_id) || state.currentStarbarId || 0;
		if( state.loggedIn === false || (state.loggedIn && state.starbars[starbarId]) )
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
		api.sendRequest({action_class: 'User', action: 'updateState', state_data: {visibility: data}});
	}
	function apiDoRequests( requests, callback ) {
		if (!requests) return;
		for (var request in requests) {
			api.setRequest( request, requests[request] );
		}
		api.sendRequests( processApiResponse );
		function processApiResponse( data ) {
			callback(data);
			if( data.common_data && data.common_data.game ) {
				state.games[data.common_data.game.economy_id] = data.common_data.game;
				comm.broadcast('state.game', data.common_data.game);
			}
		}
	}
	comm.listen('get-state', getState);
	comm.listen('api-do-requests', apiDoRequests);
	comm.listen('login', login);
	comm.listen('logout', logout);
	comm.listen('set-visibility', setVisibility);
	getUserState();
})(this, sayso.module.Api, sayso.module.comm, sayso.module.config, sayso.module.getSession);
