sayso.module.comm = (function(forge) {
	function cleanObject(data) {
		return typeof data === "object" && !(data instanceof Object) ? JSON.parse(JSON.stringify(data)) : data;
	}
	function cleanFunc(func) {
		return func ? function(value, callback) { func(cleanObject(value), callback); } : func;
	}
	function set( name, value, success, error ) {
		forge.prefs.set( name, value, cleanFunc(success), error );
	}
	function get( name, success, error ) {
		forge.prefs.get( name, cleanFunc(success), error );
	}
	function ajax( options ) {
		if( options.success )
			options.success = cleanFunc(options.success);
		forge.request.ajax( options );
	}
	function broadcast( name, value, success, error ) {
		forge.message.broadcast( name, value, cleanFunc(success), error );
	}
	function listen( name, success, error ) {
		forge.message.listen( name, cleanFunc(success), error );
	}
	return forge.is.firefox() ? {
		set: set,
		get: get,
		ajax: ajax,
		broadcast: broadcast,
		listen: listen
	} : {
		set: forge.prefs.set,
		get: forge.prefs.get,
		ajax: forge.request.ajax,
		broadcast: forge.message.broadcast,
		listen: forge.message.listen
	};
})(forge)
;
