/**
 * Sayso Init -- Load everything!
 * - Firefox can't set window variables (or inject javascript directly), but it can set the script id tag
 * - IE can't set the script id tag, but it can set window variable via injected javascript
 * - Chrome can do both (we set via inject JS)
 * - Safari ??
 */
(function () {

	// Firefox needs to get the base domain from the script tag itself (easiest way)
	var initScript = document.getElementById('sayso-init');
	if (initScript && initScript.src) {
		var firstSlash = initScript.src.indexOf('/');
		var baseDomain = initScript.src.substring(firstSlash+2, initScript.src.indexOf('/', firstSlash+2));
		window.$SaySoExtension = { base_domain : baseDomain };
	}

	// Not loaded from extension, exit!
	if (!window.$SaySoExtension) return;

	// Loads these scripts in order. Each script calls window.$SaySoExtension.loadNextScript() below when done to load the next script immediately
	var scripts = ['jquery-1.7.1.min.js', 'sayso-state.js', 'starbar-loader.js'];

	// The last_script variable ensures one direction and no repetition
	var last_script = -1;

	window.$SaySoExtension.loadNextScript = function (currentScript) {
		if (last_script == -1 || currentScript == scripts[last_script]) {
			last_script += 1;
			loadScript(last_script);
		}
	}

	function loadScript(scriptIndex) {
		var scriptElem = document.createElement('script');
		scriptElem.src = '//' + window.$SaySoExtension.base_domain + '/js/starbar/' + scripts[scriptIndex];
		document.body.appendChild(scriptElem);
	}

	// Start things off
	window.$SaySoExtension.loadNextScript();
})();
