sayso.module.frameComm = (function(global, dommsg) {
	var frameId;
	var paramName = 'frame_id';
	var listeners = {};
	function fireEvent( name, data ) {
		global.top.postMessage( JSON.stringify(['sayso-iframe-event', {name: name, frame_id: frameId, data: data}]), '*');
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
	return {
		fireEvent: fireEvent,
		listen: listen
	};
})(this, sayso.module.dommsg)
;
