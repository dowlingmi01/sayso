sayso.module.getSession = (function(global, Api, comm, dommsg, util, config, $){
	function getSession(callback) {
		if( !config.extVersion )
			comm.get('session', callback );
		else
			comm.get('session', function(session) {
				$(function(){
					readStorage( function( storageSession ) {
						if( storageSession ) {
							if( !session || (!session.timestamp && storageSession.timestamp) || (session.timestamp < storageSession.timestamp)) {
								comm.set('session', storageSession);
								callback(storageSession);
							} else
								callback(session);
						} else if( !session )
							comm.get('userKey', function(userKey) {
								if( userKey ) {
									var api = new Api(config.baseDomain);
									api.sendRequest({action_class: 'Login', action: 'migrateKey', user_key: userKey}, function(data) {
										session = data.responses['default'].variables;
										if( session && session.session_id && session.session_key ) {
											session = {id: session.session_id, key: session.session_key, timestamp: (new Date()).getTime()};
											comm.set('session', session, function() {
												comm.set('oldUserKey', userKey);
												comm.set('userKey', null);
											});
										}
										callback(session);
									});
								} else
									callback(session);
							});
						else
							callback(session);
					});
				});
		});
	}
	function readStorage(callback) {
		var iframe = global.document.createElement("iframe");
		iframe.style.cssText = "position:absolute;width:1px;height:1px;left:-9999px;";
		global.document.body.appendChild(iframe);
		util.addEventListener(iframe, 'load', function () {
			iframe.contentWindow.postMessage(JSON.stringify(['sayso-frontend-request', {name:'get-session', id: 1}]), '*');
		});
		dommsg.addHandler('background-reply', function(data) { callback(data.data); });
		iframe.src = 'http://' + config.baseDomain + '/browserapp/readStorage.html';
	}
	return getSession;
})(this, sayso.module.Api, sayso.module.comm, sayso.module.dommsg, sayso.module.util, sayso.module.config, jQuery);

