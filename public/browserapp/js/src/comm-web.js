sayso.module.comm = (function(global, $, util, dommsg, config) {
	var publicVar = {
		request: request,
		listen: listen,
		extensionPresent: false,
		ready: false
	};
	var requestTarget;
	var requests = {};
	var listeners = {};
	var id = 0;
	function isExtPresent( callback ) {
		var interval = 10;
		var maxTime = 100;
		var start = (new Date()).getTime();
		function isPresent() {
			if( global.$SaySoExtension )
				callback(true);
			else if( (new Date()).getTime() - start < maxTime )
				setTimeout(isPresent, interval);
			else
				callback(false);
		}
		setTimeout(isPresent, interval);
	}
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
		 requestTarget.postMessage(JSON.stringify(['sayso-frontend-request', params]), '*');
	}
	function listen( name, callback ) {
		listeners[name] = callback;
	}
	function backgroundReady() {
		publicVar.ready = true;
		$(global.document).trigger('sayso:comm-ready');
	}
	isExtPresent(function( extPresent ) {
		dommsg.addHandler('background-reply', handleBackgroundReply);
		dommsg.addHandler('broadcast', handleBroadcast);
		if( extPresent ) {
			publicVar.extensionPresent = true;
			requestTarget = global;
			backgroundReady();
		} else {
			var iframe = global.document.createElement("iframe");
			iframe.style.cssText = "position:absolute;width:1px;height:1px;left:-9999px;";
			global.document.body.appendChild(iframe);
			util.addEventListener(iframe, 'load', backgroundReady);
			iframe.src = 'http://' + config.baseDomain + '/browserapp/background.html';
			requestTarget = iframe.contentWindow;
		}
	});
	return publicVar;
})(this, jQuery, sayso.module.util, sayso.module.dommsg, sayso.module.config)
;
