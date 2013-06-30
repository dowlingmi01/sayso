(function() {
	var scripts = [
		'background/config.js',
		'lib/jquery-1.10.1.js',
		'util.js',
		'background/comm-forge.js',
		'background/api.js',
		'dommsg.js',
		'background/getsession.js',
		'background/main.js',
		'background/getscript.js'
	];
	var i = 0;
	var loadNextScript = forge.is.chrome() ? loadNextScriptChrome : loadNextScriptOthers;
	function advance() {
		i++;
		if( i < scripts.length )
			loadNextScript();
	}
	function loadNextScriptChrome() {
		var scriptEl = document.createElement('script');
		scriptEl.src = 'https://' + sayso.baseDomain + '/browserapp/js/src/' + scripts[i] /* +
			'?_=' + ( new Date() ).getTime() */;
		if(scriptEl.addEventListener)
			scriptEl.addEventListener('load', advance, false);
		else
			scriptEl.attachEvent('onload', advance);
		document.head.appendChild(scriptEl);
	}
	function loadNextScriptOthers() {
		var url = 'http://' + sayso.baseDomain + '/browserapp/js/src/' + scripts[i];
		forge.request.get( url + '?_=' + ( new Date() ).getTime(),
			function(content) {
				content = content + (forge.is.ie() ? '' : '\n//@ sourceURL=' + url);
				if( window.execScript )
					window.execScript(content)
				else
					eval(content);
				advance();
			}
		);
	}
	loadNextScript();
}())
;
