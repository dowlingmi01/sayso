(function () {
	if (document.body) {
		var baseDomain = 'app.saysollc.com';

		var saysoGlobal = document.createElement('script');
		saysoGlobal.text = "window.$SaySoExtension = { base_domain: '" + baseDomain + "', ext_version: '2.0.0.0', ext_browser: 'Safari' }";
		document.body.appendChild(saysoGlobal);

		var saysoInit = document.createElement('script');
		saysoInit.src = '//' + baseDomain + '/js/starbar/sayso-init.js';
		document.body.appendChild(saysoInit);
	}
})();
