if (document.body) {
	var saysoGlobal = document.createElement('script');
	var txt = "window.$SaySoExtension = { base_domain: '" + sayso.baseDomain + "', ext_version: '" + sayso.version + "' }";
	
	if (forge.is.firefox())
		saysoGlobal.textContent = txt;
	else
		saysoGlobal.text = txt;
		
	document.body.appendChild(saysoGlobal);

	var saysoInit = document.createElement('script');
	saysoInit.src = '//' + sayso.baseDomain + '/js/starbar/sayso-init.js';
	document.body.appendChild(saysoInit);
}

//@ sourceURL=sayso-content.js
