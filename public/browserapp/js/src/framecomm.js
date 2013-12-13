sayso.module.frameComm = (function(global, $, dommsg, api) {
	var frames = [];
	var frameId = 0;
	function handleApiRequests( data, source ) {
		api.doRequests(data.requests, function(response) {
			if( data.id )
				source.postMessage(JSON.stringify(['sayso-iframe-api-response', {id: data.id, data:response}]), '*');
		});
	}
	function handleEvent( data ) {
		$(global.document).trigger('sayso:iframe-' + data.name, [{frame_id: data.frame_id, data: data.data}]);
	}
	function setURL( $iframe, url, useParam ) {
		frameId++;
		frames[frameId] = $iframe[0];
		var sep = useParam ? (url.indexOf('?') >= 0 ? '&' : '?') : '#';
		url = url + sep + 'frame_id=' + frameId;
		$iframe.attr('src', url);
		return frameId;
	}
	function fireEvent( frameId, name, data ) {
		frames[frameId].contentWindow.postMessage(JSON.stringify(['sayso-iframe-event', {name: name, data: data}]), '*');
	}
	function install() {
		dommsg.addHandler('iframe-api-requests', handleApiRequests);
		dommsg.addHandler('iframe-event', handleEvent);
	}
	return {
		install: install,
		setURL: setURL,
		fireEvent: fireEvent
	};
})(this, jQuery, sayso.module.dommsg, sayso.module.api)
;
