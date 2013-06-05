sayso.module.state = (function(global, $, comm) {
	var publicVar = {
		state: null,
		ready: false,
		apiCall: apiCall
	};
	var stateListeners = {
		login: function(data) {
			publicVar.state = data;
			$(global.document).trigger('sayso:state-login');
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
	function apiCall( request, callback ) {
		comm.request('api-call', request, callback );
	}
	function gotState(data) {
		publicVar.state = data;
		publicVar.ready = true;
		$(global.document).trigger('sayso:state-ready');
	}
	function start() {
		comm.request('get-state', {starbar_id: 4}, gotState);
	}

	for( var name in stateListeners )
		comm.listen('state.' + name, stateListeners[name]);

	if( comm.ready )
		start();
	else
		$(global.document).on('sayso:comm-ready', start);

	return publicVar;
})(this, jQuery, sayso.module.comm)
;
