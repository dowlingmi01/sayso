forge.logging.info("Say.So Bar " + sayso.version);
forge.request.get( "http://" + sayso.baseDomain + "/js/starbar/sayso-content.js"
			, function(content) {
				eval(content);
			}
			, function(errObject) {
				forge.logging.error(errObject.message);
			}
		);
