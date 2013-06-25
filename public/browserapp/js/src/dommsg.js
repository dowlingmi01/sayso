sayso.module.dommsg = (function(global, util) {
	var handlers = {};
	function handleMessage( event ) {
		try {
			var data = JSON.parse(event.data);
			if( typeof data === 'string' )
				data = JSON.parse(data);

			var prefix = 'sayso-';
			var evt;
			if( data[0] && data[0].slice && data[0].slice(0, prefix.length) === prefix && handlers[(evt=data[0].slice(prefix.length))])
				handlers[evt](data[1], event.source);
		} catch( e ) {
		}
	}
	function addHandler( name, fn ) {
		handlers[name] = fn;
	}

	util.addEventListener(global, 'message', handleMessage);
	return {
		addHandler: addHandler
	};
})(this, sayso.module.util)
;
