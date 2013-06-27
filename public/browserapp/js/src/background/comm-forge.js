sayso.module.comm = (function(forge) {
	return {
		set: forge.prefs.set,
		get: forge.prefs.get,
		ajax: forge.request.ajax,
		broadcast: forge.message.broadcast,
		listen: forge.message.listen
	};
})(forge)
;
