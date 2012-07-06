(function() {
	window.saysoConf = {
		baseDomain: getBaseDomain(),
		bn: getBrowserNameVersion(),
		locationCookie: getLocationCookie()
	}
	function getBaseDomain() {
		if( location.host.match('saysollc\.com|local\.sayso\.com') )
			return location.host;
		else
			return 'app.saysollc.com';
	}
	function getBrowserNameVersion() {
		var bn = {};
		var ua = navigator.userAgent;
		if( !ua ) {
			bn.browser = "unknown";
			bn.isSupported = false;
			return bn;
		}
		var mobileStrs = ["Android", "webOS", "iPhone", "iPod", "iPad"];
		var identStrs = ["Chrome", "Safari", "Firefox", "MSIE"];
		var versionStrs = ["Chrome", "Version", "Firefox", "MSIE"];
		var minVer = [1.0, 5.01, 10.0, 8.0];
		var i, j;
		for( i = 0; i < mobileStrs.length; i++ )
			if( ua.indexOf(mobileStrs[i]) >= 0 ) {
			bn.isMobile = true;
			bn.isSupported = false;
			return bn;
		}
		for( i = 0; i < identStrs.length; i++ )
			if( (j = ua.indexOf(identStrs[i])) >= 0 ) {
				bn.browser = identStrs[i].toLowerCase();
				var ver = 0;
				if( (j = ua.indexOf(versionStrs[i])) > 0 ) {
					ver = parseFloat(ua.substring(j + versionStrs[i].length + 1));
					if( ver )
						bn.version = ver;
				}
					
				bn.isSupported = ver >= minVer[i];
				return bn;
			}
		bn.browser = "unknown";
		bn.isSupported = false;
		return bn;
	}

	function getLocationCookie() {
		var locationCookie = getCookie( 'sayso-location' );
		
		if( !locationCookie ) {
			locationCookie = getRandomToken();
			setCookie( 'sayso-location', locationCookie, 365 );
		}
		
		return locationCookie;
	}

	function getCookie (find) {
		var cookies = document.cookie.split(';');
		for(var i = 0; i < cookies.length; i++) {
			var name = cookies[i].slice(0, cookies[i].indexOf('=')).trim();
			if (name === find) {
				return cookies[i].slice(cookies[i].indexOf('=')+1).trim();
			}
		}
		return '';
	}

	function setCookie(name, value, days) {
		var dateTime = new Date();
		if (days) {
			dateTime.setDate(dateTime.getDate() + days);
		}
		var value = escape(value) + (days ? '; expires=' + dateTime.toUTCString() : '') + '; path=/';
		document.cookie = name + '=' + value;
	}

	function getUrlParam (name) {
		if (!this.params) {
			this.params = {};
			var e,
			a = /\+/g,
			r = /([^&=]+)=?([^&]*)/g,
			d = function (s) { return decodeURIComponent(s.replace(a, " ")); },
			q = window.location.search.substring(1);

			while (e = r.exec(q)) {
				this.params[d(e[1])] = d(e[2]);
			}
		}
		return this.params[name];
	}
	
	function getRandomToken() {
		var s = [],
			characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		for (var i = 0; i < 32; i++) {
			s[i] = characters[Math.floor(Math.random() * characters.length)];
		}
		return s.join('');
	}
})();