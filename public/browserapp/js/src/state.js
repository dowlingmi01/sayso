sayso.module.state = (function(global, $, comm, config, util, api) {
	var publicVar = {
		state: null,
		ready: false,
		login: login,
		logout: logout,
		loginMachinimaReload: loginMachinimaReload,
		subscribeStarbar: subscribeStarbar,
		refresh: refresh,
		setVisibility: setVisibility
	};
	var urlParams = util.urlParams(config.location.search.slice(1));
	var stateListeners = {
		login: function() {
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
		visibility: function(data) {
			publicVar.state.visibility = data;
			$(global.document).trigger('sayso:state-visibility');
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
		},
		surveyCounts: function(data) {
			if( data.starbar_id === publicVar.state.starbar.id ) {
				publicVar.state.surveyCounts = data.surveyCounts;
				$(global.document).trigger('sayso:state-surveyCounts');
			}
		}
	};
	function login( email, password, callback ) {
		comm.request('login', { email: email, password: password}, callback);
	}
	function logout( callback ) {
		comm.request('logout', null, callback);
	}
	function loginMachinimaReload() {
		requestState('login');
	}
	function gotState(eventName) {
		return function(data) {
			publicVar.state = data;
			publicVar.ready = true;
			$(global.document).trigger('sayso:state-' + eventName);
		};
	}
	function requestState( eventName ) {
		var params = {starbar_id: config.defaultStarbarId};
		if( publicVar.machinimareload )
			params.machinimareload = publicVar.machinimareload;
		comm.request('get-state', params, gotState( eventName ));
	}
	function setVisibility( visibility ) {
		comm.request('set-visibility', visibility);
	}
	function refresh() {
		requestState('starbar');
	}
	function subscribeStarbar(starbarId, callback) {
		var params = {
			action_class : 'starbar',
			action : 'subscribeStarbar',
			starbar_id: starbarId
		};
		if( publicVar.machinimareload )
			params.data = { digest: publicVar.machinimareload.digest };
		api.doRequest( params, function( response ) {
			if( response.responses['default'].variables.status )
				refresh();
			if( callback )
				callback( response );
		});

	}

	if( urlParams.machinimareload_email && urlParams.machinimareload_digest ) {
		publicVar.machinimareload = {
			email: urlParams.machinimareload_email,
			digest: urlParams.machinimareload_digest
		};
	}
	for( var name in stateListeners )
		comm.listen('state.' + name, stateListeners[name]);

	if( comm.ready )
		requestState('ready');
	else
		$(global.document).on('sayso:comm-ready', function() { requestState('ready'); });

	return publicVar;
})(this, jQuery, sayso.module.comm, sayso.module.config, sayso.module.util, sayso.module.api)
;
