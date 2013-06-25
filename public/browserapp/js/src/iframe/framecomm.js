sayso.module.frameComm = (function(global) {
	function fireEvent( name, data ) {
		global.top.postMessage( JSON.stringify(['sayso-iframe-event', {name: name, data: data}]), '*');
	}
	return {
		fireEvent: fireEvent
	};
})(this)
;
