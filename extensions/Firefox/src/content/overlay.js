(function () {
	var baseDomain = 'app.saysollc.com';

	// See https://developer.mozilla.org/en/Code_snippets/On_page_load

	function init() {
		var appcontent = document.getElementById("appcontent");   // browser
		if (appcontent){
			appcontent.addEventListener("DOMContentLoaded", onPageLoad, true);
		}
	}

	function onPageLoad(aEvent) {
		doc = aEvent.originalTarget;
		win = doc.defaultView;
		if (doc.body) {
			var saysoInit = doc.createElement('script');
			saysoInit.src = '//' + baseDomain + '/js/starbar/sayso-init.js';
			saysoInit.id = 'sayso-init';
			doc.body.appendChild(saysoInit);
		}
		//aEvent.originalTarget.defaultView.addEventListener("unload", function(event){ myExtension.onPageUnload(event); }, true);
	}

	/*function onPageUnload (aEvent) {
		// do something
	}*/

	window.addEventListener("load", function(e) {
	    init();
	}, false);
})();
