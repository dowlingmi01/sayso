sayso.module.api = (function(global, dommsg) {
	var requests = {};
	var id = 0;
	function doRequest( request, callback ) {
		doRequests( { 'default': request }, callback );
	}
	function doRequests( data, callback ) {
		var params = {requests: data};
		if( callback ) {
			requests[++id] = callback;
			params.id = id;
		}
		global.parent.postMessage(JSON.stringify(['sayso-iframe-api-requests', params]), '*');
	}
	function handleApiResponse( data ) {
		if( data.id && requests[data.id] ) {
			requests[data.id](data.data);
			delete requests[data.id];
		}
	}
	dommsg.addHandler('iframe-api-response', handleApiResponse);
	return {
		doRequest: doRequest,
		doRequests: doRequests
	};
})(this, sayso.module.dommsg)
;
