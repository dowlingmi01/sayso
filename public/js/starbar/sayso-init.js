/**
 * Sayso Init -- Load everything!
 */
(function () {
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
