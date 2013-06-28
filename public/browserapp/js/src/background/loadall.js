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
	function loadNextScript() {
		var scriptEl = document.createElement('script');
		scriptEl.src = 'https://' + sayso.baseDomain + '/browserapp/js/src/' + scripts[i] +
			'?_=' + ( new Date() ).getTime();
		scriptEl.addEventListener('load', function() {
			i++;
			if( i < scripts.length )
				loadNextScript();
		});
		document.head.appendChild(scriptEl);
	}
	loadNextScript();
}())
;
