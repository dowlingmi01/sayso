// Based on http://www.oxymoronical.com/blog/2011/01/Playing-with-windows-in-restartless-bootstrapped-extensions

const Cc = Components.classes;
const Ci = Components.interfaces;

var WindowListener = {
	onPageLoad: function (aEvent) {
		var baseDomain = 'app.saysollc.com';
		var doc = aEvent.originalTarget;
		var win = doc.defaultView;
		if (doc.body) {
			var saysoGlobal = doc.createElement('script');
			saysoGlobal.textContent = "window.$SaySoExtension = { base_domain: '" + baseDomain + "', ext_version: '2.0.0.0', ext_browser: 'Firefox' }";
			doc.body.appendChild(saysoGlobal);

			var saysoInit = doc.createElement('script');
			saysoInit.src = '//' + baseDomain + '/js/starbar/sayso-init.js';
			saysoInit.id = 'sayso-init';
			doc.body.appendChild(saysoInit);
		}
	},
	
	setupBrowserUI: function(window) {
		var document = window.document;

		var appcontent = document.getElementById("appcontent");   // browser
		if (appcontent){
			appcontent.addEventListener("DOMContentLoaded", this.onPageLoad, true);
		}
	},

	tearDownBrowserUI: function(window) {
		var document = window.document;

		var appcontent = document.getElementById("appcontent");   // browser
		if (appcontent){
			appcontent.removeEventListener("DOMContentLoaded", this.onPageLoad, true);
		}
	},

	// nsIWindowMediatorListener functions
	onOpenWindow: function(xulWindow) {
		// A new window has opened
		var domWindow = xulWindow.QueryInterface(Ci.nsIInterfaceRequestor)
			.getInterface(Ci.nsIDOMWindowInternal);

		// Wait for it to finish loading
		domWindow.addEventListener("load", function listener() {
			domWindow.removeEventListener("load", listener, false);

			// If this is a browser window then setup its UI
			if (domWindow.document.documentElement.getAttribute("windowtype") == "navigator:browser")
				WindowListener.setupBrowserUI(domWindow);
		}, false);
	},
	
	onCloseWindow: function(xulWindow) {
	},

	onWindowTitleChange: function(xulWindow, newTitle) {
	}
	
};

function startup(data, reason) {
	var wm = Cc["@mozilla.org/appshell/window-mediator;1"].
		getService(Ci.nsIWindowMediator);

	// Get the list of browser windows already open
	var windows = wm.getEnumerator("navigator:browser");
	while (windows.hasMoreElements()) {
		var domWindow = windows.getNext().QueryInterface(Ci.nsIDOMWindow);

		WindowListener.setupBrowserUI(domWindow);
	}

	// Wait for any new browser windows to open
	wm.addListener(WindowListener);
}

function shutdown(data, reason) {
	// When the application is shutting down we normally don't have to clean
	// up any UI changes made
	if (reason == APP_SHUTDOWN)
		return;

	var wm = Cc["@mozilla.org/appshell/window-mediator;1"].
		getService(Ci.nsIWindowMediator);

	// Get the list of browser windows already open
	var windows = wm.getEnumerator("navigator:browser");
	while (windows.hasMoreElements()) {
		var domWindow = windows.getNext().QueryInterface(Ci.nsIDOMWindow);

		WindowListener.tearDownBrowserUI(domWindow);
	}

	// Stop listening for any new browser windows to open
	wm.removeListener(WindowListener);
}