sayso.module.getSession = (function(global, Api, comm, dommsg, util, config, $){
	function getSession(callback) {
		comm.get('session', function(session) {
			if( session || !config.extVersion )
				callback(session);
			else
				comm.get('userKey', function(userKey) {
					//TODO: remove false when Login::migrateKey endpoint is implemented
					if( false && userKey ) {
						var api = new Api(config.baseDomain);
						api.sendRequest({action_class: 'Login', action: 'migrateKey', user_key: userKey}, function(data) {
							var session = data.responses['default'].variables;
							if( session ) {
								comm.set('session', session, function() {
									comm.set('userKey', null);
								});
								callback(session);
							}
						});
					} else
						$(function(){readStorage(callback);});
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
		dommsg.addHandler('background-reply', function (data) {
			if( data.data ) {
				comm.set('session', data.data);
				callback(data.data);
			}
		});
		iframe.src = 'http://' + config.baseDomain + '/browserapp/readStorage.html';
	}
	return getSession;
})(this, sayso.module.Api, sayso.module.comm, sayso.module.dommsg, sayso.module.util, sayso.module.config, jQuery);

