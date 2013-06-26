sayso.module.frameApp = (function(global, $, api, comm) {
	function runAction(data) {
		if ('action' in data && data['action'] in actions) {
			actions[data['action']](data);
		}
	}

	var actions = {
		'display-poll': function(data) {
			$('body').html('stuff');
			comm.fireEvent('poll-loaded', {height: 123});
		}
	}

	comm.listen('init-action', runAction);
	comm.fireEvent('ready');
})(this, jQuery, sayso.module.api, sayso.module.frameComm)
;
