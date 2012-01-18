(function () {

	if (window.saysoClientSetup) return;
	if (top !== self) return;

	function getCookie (find) {
		var cookies = document.cookie.split(';');
		for(var i = 0; i < cookies.length; i++) {
			var nameValue = cookies[i].split('=');
			var name = nameValue[0].trim();
			if (name === find) {
				return nameValue[1].trim();
			}
		}
		return '';
	}

	var partners = {
		hellomusic : {
			domain : 'hellomusic.com',
			idCookieName : 'MyEmail',
			idType : 'email',
			loggedInCookieName : 'CHOMPUID'
		},
		hellomusic_test : {
			domain : 'saysollc.com',
			idCookieName : 'MyEmail',
			idType : 'email',
			loggedInCookieName : 'CHOMPUID'
		}
	};

	var partner = '';

	for (partner in partners) {

		if (location.host.match(partners[partner].domain)) {

			if (!String.prototype.trim) {
				String.prototype.trim = function() {
					return this.replace(/^\s+|\s+$/g,'');
				};
			}

			if (!window.$SQ.sayso) window.$SQ.sayso = {};
			window.$SQ.sayso.client = {
				name : partner,
				uuid : getCookie(partners[partner].idCookieName),
				uuidType : partners[partner].idType, // email, username, hash, integer
				userLoggedIn : getCookie(partners[partner].loggedInCookieName) ? true : false
			};

			if (window.$SQ.sayso.log) {
				window.$SQ.sayso.log('Detected client site: ' + partner);
			}
			break;
		}
	}

	window.saysoClientSetup = true;

})();