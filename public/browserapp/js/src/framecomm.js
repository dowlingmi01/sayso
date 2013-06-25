sayso.module.frameComm = (function(global, $, dommsg, api) {
	function handleApiRequests( data, source ) {
		api.doRequests(data.requests, function(response) {
			if( data.id )
				source.postMessage(JSON.stringify(['sayso-iframe-api-response', {id: data.id, data:response}]), '*');
		});
	}
	function handleEvent( data ) {
		$(global.document).trigger('sayso:iframe-' + data.name, data.data);
	}
	dommsg.addHandler('iframe-api-requests', handleApiRequests);
	dommsg.addHandler('iframe-event', handleEvent);
})(this, jQuery, sayso.module.dommsg, sayso.module.api)
;
