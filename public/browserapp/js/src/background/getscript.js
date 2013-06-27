(function(forge, comm, config){
	var scripts = [];
	function getScript( scriptName, callback ) {
		if( scriptName === 'starbar/sayso-content.js' )
			scriptName = 'loadall.js';
		if( !scripts[scriptName] ) {
			var url = 'http://' + config.baseDomain + '/browserapp/js/src/' + scriptName;
			forge.request.get( url + '?_=' + ( new Date() ).getTime(),
				function(content) {
					scripts[scriptName] = content + (forge.is.ie() ? '' : '\n//@ sourceURL=' + url);
					callback( scripts[scriptName] );
				}
			);
		} else
			callback( scripts[scriptName] );
	}
	comm.listen('get-script', getScript);
})(forge, sayso.module.comm, sayso.module.config);
