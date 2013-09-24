sayso.module.dommsg = (function(global, util) {
	var handlers = {};
	function handleMessage( event ) {
		var data;
		try {
			data = JSON.parse(event.data);
			if( typeof data === 'string' )
				data = JSON.parse(data);
		} catch( e ) {
		}

		var prefix = 'sayso-';
		var evt;
		if( data && data[0] && data[0].slice && data[0].slice(0, prefix.length) === prefix && handlers[(evt=data[0].slice(prefix.length))])
			handlers[evt](data[1], event.source);
	}
	function addHandler( name, fn ) {
		handlers[name] = fn;
	}
	function resetHandleMessage() {
		util.removeEventListener(global, 'message', handleMessage);
		util.addEventListener(global, 'message', handleMessage);
	}

	util.addEventListener(global, 'message', handleMessage);
	return {
		addHandler: addHandler,
		resetHandleMessage: resetHandleMessage
	};
})(this, sayso.module.util)
;
