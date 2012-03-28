forge.logging.info("Say.So Bar Background " + sayso.version);
forge.request.get( "http://" + sayso.baseDomain + "/js/starbar/sayso-background.js"
			, function(content) {
				eval(content);
			}
			, function(errObject) {
				forge.logging.error(errObject.message);
			}
		);
