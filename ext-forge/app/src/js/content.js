forge.logging.info("Say.So " + sayso.version);
sayso.location = {
	'hash': location.hash,
	'host': location.host,
	'hostname': location.hostname,
	'href': location.href,
	'pathname': location.pathname,
	'port': location.port,
	'protocol': location.protocol,
	'search': location.search
};
function getContentScript() {
	if( !sayso.gotContentScript ) {
		forge.message.broadcastBackground( "get-script", "starbar/sayso-content.js"
					, function(content) {
						if( !sayso.gotContentScript ) {
							sayso.gotContentScript = true;
							if( window.execScript )
								window.execScript(content)
							else
								eval(content);
						}
					}
					, function(errObject) {
						forge.logging.error(errObject.message);
					}
				);
		setTimeout(getContentScript, 500);
	}
}
getContentScript();