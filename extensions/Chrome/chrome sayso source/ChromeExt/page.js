(function () {
	if (document.body) {
		var baseDomain = 'hamza.saysollc.com';

		var saysoGlobal = document.createElement('script');
		saysoGlobal.text = "window.$SaySoExtension = { base_domain: '" + baseDomain + "' }";
		document.body.appendChild(saysoGlobal);

		var saysoInit = document.createElement('script');
		saysoInit.src = '//' + baseDomain + '/js/starbar/sayso-init.js';
		document.body.appendChild(saysoInit);
	}
})();
