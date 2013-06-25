sayso.module.state = (function(global, $, comm) {
	//TODO: this should come from a config module
	var starbarId = 4;

	var publicVar = {
		state: null,
		ready: false,
		login: login,
		logout: logout
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
			if( data.economy_id === publicVar.state.starbar.economy_id ) {
				publicVar.state.game = data;
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
			publicVar.in_iframe = (window.top !== window); // @todo needs fix for firefox?
			$(global.document).trigger('sayso:state-' + eventName);
		};
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
