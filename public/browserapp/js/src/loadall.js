(function() {
	var scripts = [
		'config.js',
		'lib/jquery-1.10.1.js',
		'lib/handlebars.js',
		'util.js',
		'dommsg.js',
		'comm-forge.js',
		'framecomm.js',
		'state.js',
		'api.js',
		'browserapp.js'
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
