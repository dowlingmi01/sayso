(function(global, Api, comm){
	var baseDomain = (global.sayso && global.sayso.baseDomain) || global.location.host;
	var state = {
		starbars: {},
		games: {},
		notifications: {},
		loggedIn: null
	};
	var pendingStarbars = {};
	var pendingRequests = {};
	function getUserState() {
		comm.get('user', function( user ) {
			user = user || {id: 2, session_key: '3LFB9PH7PQICJZ1VC2I8PELNO14100M9'};
			if( user && user.id && user.session_key ) {
				var api = new Api(baseDomain, user.id, user.session_key);
				api.setRequest( 'user', {action_class: 'User', action: 'getUser'} );
				api.setRequest( 'state', {action_class: 'User', action: 'getState'} );
				api.sendRequests( function(data) {
					state.loggedIn = true;
					state.user = user;
					state.profile = data.responses.user.records[user.id];
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
				pendingRequests = pendingStarbars = {};
			}
		} );
	}
	function getStarbarState( starbarId ) {
		if( pendingStarbars[starbarId] )
			return;
		pendingStarbars[starbarId] = true;
		var api = new Api( baseDomain, state.user.id, state.user.session_key);
		api.setRequest( 'game', {action_class: 'Game', action: 'getGame', starbar_id: starbarId} );
		api.setRequest( 'notifications', {action_class: 'Notification', action: 'getUserNotifications', starbar_id: starbarId} );
		api.setRequest( 'starbar', {action_class: 'Starbar', action: 'getStarbar', starbar_id: starbarId} );
		api.sendRequests( function(data) {
			state.starbars[starbarId] = data.responses.starbar.records[starbarId];
			state.starbars[starbarId].id = starbarId;
			state.games[starbarId] = data.responses.game.variables.game;
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
		return {
			loggedIn: state.loggedIn,
			profile: state.profile,
			starbar: state.starbars[starbarId],
			notifications: state.notifications[starbarId],
			game: state.games[starbarId]
		}
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
	function apiCall( data, callback ) {
		var api = new Api(baseDomain, state.user.id, state.user.session_key);
		var starbarId = data.starbar_id;
		api.sendRequest( data, processApiResponse );
		function processApiResponse( data ) {
			callback(data);
			if( starbarId && data.common_data && data.common_data.game ) {
				state.games[starbarId] = data.common_data.game;
				comm.broadcast('state.game', { starbar_id: starbarId, game: data.common_data.game });
			}
		}

	}
	comm.listen('get-state', getState);
	comm.listen('api-call', apiCall);
	getUserState();
})(this, sayso.module.Api, sayso.module.comm);
