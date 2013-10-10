sayso.module.frameComm = (function(global, dommsg, util) {
	var frameId;
	var paramName = 'frame_id';
	var listeners = {};
	function fireEvent( name, data ) {
		global.parent.postMessage( JSON.stringify(['sayso-iframe-event', {name: name, frame_id: frameId, data: data}]), '*');
	}
	function listen( name, callback ) {
		listeners[name] = callback;
	}
	function handleEvent( data ) {
		if( listeners[data.name] )
			listeners[data.name](data.data);
	}

	dommsg.addHandler('iframe-event', handleEvent);
	if( global.location.hash.slice(0, paramName.length + 2) === '#' + paramName + '=' )
		frameId = parseInt(global.location.hash.slice(paramName.length + 2), 10);
	else {
		var params = util.urlParams(global.location.search.slice(1));
		if( params.frame_id )
			frameId = parseInt(params.frame_id, 10);
	}
	return {
		fireEvent: fireEvent,
		listen: listen
	};
})(this, sayso.module.dommsg, sayso.module.util)
;
