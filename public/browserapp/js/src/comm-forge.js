sayso.module.comm = (function(global, $, forge) {
	$(global.document).trigger('sayso:comm-ready');
	return {
		request: forge.message.broadcastBackground,
		listen: forge.message.listen,
		ready: true
	};
})(this, jQuery, forge)
;
