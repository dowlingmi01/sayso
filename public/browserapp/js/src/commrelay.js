sayso.module.commrelay = (function(global, forge, dommsg) {
	var names = ['state.game', 'state.logout', 'state.login', 'state.profile', 'state.notifications'];
	function handleFrontEndRequest(data) {
		forge.message.broadcastBackground(data.name, data.data, function(response) {
			global.postMessage(JSON.stringify(['sayso-background-reply', {id: data.id, data: response}]), '*');
		});
	}
	function getListener(name) {
		return function(data) {
			global.postMessage(JSON.stringify(['sayso-broadcast', {name: name, data: data}]), '*');
		};
	}
	function install() {
		dommsg.addHandler('frontend-request', handleFrontEndRequest);
		for( var i = 0; i < names.length; i++ ) {
			forge.message.listen(names[i], getListener(names[i]));
		}
	}
	return {
		install: install
	};
})(this, forge, sayso.module.dommsg)
;
