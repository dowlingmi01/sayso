(function(global, comm){
	comm.listen('get-session', function(unused, callback) {
		comm.get('session', callback);
	});
})(this, sayso.module.comm);
