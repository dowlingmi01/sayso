(function() {
	var scripts = [
		'config.js',
		'lib/jquery-1.10.1.js',
		'lib/jquery.html5-placeholder-shim.js',
		'lib/handlebars.js',
		'util.js',
		'dommsg.js',
		'comm-forge.js',
		'api.js',
		'framecomm.js',
		'state.js',
		'browserapp.js',
		'commrelay.js',
		'main-forge.js'
	];
	sayso.module = {};
	var i = 0;
	function loadNextScript() {
		forge.message.broadcastBackground( "get-script", scripts[i], function(content) {
			if( window.execScript )
				window.execScript(content);
			else
				eval(content);
			i++;
			if( i < scripts.length )
				loadNextScript();
		});
	}
	loadNextScript();
}())
;
