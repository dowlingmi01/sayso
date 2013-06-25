sayso.module.api = (function(comm) {
	function doRequest( request, callback ) {
		doRequests( { 'default': request }, callback);
	}
	function doRequests( requests, callback ) {
		comm.request('api-do-requests', requests, callback );
	}
	return {
		doRequest: doRequest,
		doRequests: doRequests
	};
})(sayso.module.comm)
;
