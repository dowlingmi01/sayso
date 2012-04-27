forge.logging.info("Say.So Bar " + sayso.version);
function getContentScript() {
	if( !sayso.gotContentScript ) {
		forge.message.broadcastBackground( "get-script", "starbar/sayso-content.js"
					, function(content) {
						if( !sayso.gotContentScript ) {
							sayso.gotContentScript = true;
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