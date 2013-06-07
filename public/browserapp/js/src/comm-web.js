sayso.module.comm = (function(global, $, util) {
	var baseDomain = 'local.saysollc.com';
	var iframe = global.document.createElement("iframe");
	var requests = {};
	var listeners = {};
	var id = 0;
	var ready = false;
	function handleMessage( event ) {
		try {
			var data = JSON.parse(event.data);
			if( typeof data == 'string' )
				data = JSON.parse(data);
			if( data[0] && data[0] == 'sayso-background-reply' && data[1] && data[1].id && requests[data[1].id]) {
				requests[data[1].id](data[1].data);
				delete requests[data[1].id];
			} else if( data[0] && data[0] == 'sayso-broadcast' && data[1] && data[1].name && listeners[data[1].name]) {
				listeners[data[1].name](data[1].data);
			}
		} catch( e ) {
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
	util.addEventListener(global, 'message', handleMessage);
	iframe.src = 'http://' + baseDomain +  '/browserapp/background.html';
	return {
		request: request,
		listen: listen,
		ready: ready
	};
})(this, jQuery, sayso.module.util)
;
