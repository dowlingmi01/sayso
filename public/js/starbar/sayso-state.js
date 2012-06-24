/**
 * Sayso State -- Authenticate before loading starbar
 */
(function () {
	if( sayso.client ) {
		ajaxData.client_name = sayso.client.name;
		if( sayso.client.meta && sayso.client.meta.sendKeys ) {
			var clientKeys = {};
			for( var i = 0; i < sayso.client.meta.sendKeys.length; i++ )
				clientKeys[sayso.client.meta.sendKeys[i]] = getCookie(sayso.client.meta.sendKeys[i]);
			ajaxData.client_keys = clientKeys;
		}
	}
})();
