sayso.module.state = (function(global, $, comm) {
	//TODO: this should come from a config module
	var starbarId = 4;

	var publicVar = {
		state: null,
		ready: false,
		login: login,
		logout: logout,
		apiCall: apiCall,
		apiAddRequest: apiAddRequest,
		apiAddRequests: apiAddRequests,
		apiSendRequest: apiSendRequest,
		apiSendRequests: apiSendRequests
	};
	var stateListeners = {
		login: function(data) {
			requestState('login');
		},
		logout: function(data) {
			publicVar.state = data;
			$(global.document).trigger('sayso:state-logout');
		},
		profile: function(data) {
			publicVar.state.profile = data;
			$(global.document).trigger('sayso:state-profile');
		},
		game: function(data) {
			if( data.starbar_id === publicVar.state.starbar.id ) {
				publicVar.state.game = data.game;
				$(global.document).trigger('sayso:state-game');
			}
		},
		notifications: function(data) {
			if( data.starbar_id === publicVar.state.starbar.id ) {
				publicVar.state.notifications = data.notifications;
				$(global.document).trigger('sayso:state-notifications');
			}
		}
	};
	// resets api requests before sending
	function apiAddRequest( requestName, requestData ) {
		var requests = {};
		requests[requestName] = requestData;
		comm.request('api-add-requests', requests );
	}
	function apiAddRequests( requests ) {
		comm.request('api-add-requests', requests );
	}
	// resets api requests before sending
	function apiSendRequest( requestName, requestData, callback ) {
		apiAddRequest( requestName, requestData );
		apiSendRequests( callback );
	}
	function apiSendRequests( callback ) {
		comm.request('api-send-requests', null, callback );
	}
	function apiCall( request, callback ) {
		comm.request('api-call', request, callback );
	}
	function login( email, password, callback ) {
		comm.request('login', { email: email, password: password}, callback);
	}
	function logout( callback ) {
		comm.request('logout', null, callback);
	}
	function gotState(eventName) {
		return function(data) {
			publicVar.state = data;
			publicVar.ready = true;
			publicVar.in_iframe = (window.top != window); // @todo needs fix for firefox?
			$(global.document).trigger('sayso:state-' + eventName);
		}
	}
	function requestState( eventName ) {
		comm.request('get-state', {starbar_id: starbarId}, gotState( eventName ));
	}

	for( var name in stateListeners )
		comm.listen('state.' + name, stateListeners[name]);

	if( comm.ready )
		requestState('ready');
	else
		$(global.document).on('sayso:comm-ready', function() { requestState('ready'); });

	return publicVar;
})(this, jQuery, sayso.module.comm)
;
