sayso.module.comm = (function(global, $, util, dommsg) {
	var iframe = global.document.createElement("iframe");
	var requests = {};
	var listeners = {};
	var id = 0;
	var ready = false;
	function handleBackgroundReply( data ) {
		if( data.id && requests[data.id] ) {
			requests[data.id](data.data);
			delete requests[data.id];
		}
	}
	function handleBroadcast( data ) {
		if( data.name && listeners[data.name] ) {
			listeners[data.name](data.data);
		}
	}
	function request( name, data, callback ) {
		var params = {name: name, data: data};
		if( callback ) {
			requests[++id] = callback;
			params.id = id;
		}
		iframe.contentWindow.postMessage(JSON.stringify(['sayso-frontend-request', params]), '*');
	}
	function listen( name, callback ) {
		listeners[name] = callback;
	}
	function backgroundReady() {
		ready = true;
		$(global.document).trigger('sayso:comm-ready');
	}
	iframe.style.cssText = "position:absolute;width:1px;height:1px;left:-9999px;";
	global.document.body.appendChild(iframe);
	util.addEventListener(iframe, 'load', backgroundReady);
	dommsg.addHandler('background-reply', handleBackgroundReply);
	dommsg.addHandler('broadcast', handleBroadcast);
	iframe.src = 'http://' + global.sayso.base_domain +  '/browserapp/background.html';
	return {
		request: request,
		listen: listen,
		ready: ready
	};
})(this, jQuery, sayso.module.util, sayso.module.dommsg)
;
