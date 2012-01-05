
(function () {

	// add trim function if needed
	if(typeof String.prototype.trim !== 'function') {
		String.prototype.trim = function() {
			return this.replace(/^\s+|\s+$/g, '');
		};
	}

	if (getCookie('sayso-installed')) return;

	if (!window.sayso) window.sayso = {};
	if (!window.sayso.baseDomain) {
		window.sayso.baseDomain = 'app.saysollc.com';
		window.sayso.environment = 'PROD';
	}

	var sayso = window.sayso;

	var installParam = getUrlParam('sayso-install'),
		installCookie = getCookie('sayso-install');

	// if app is already loaded, go no further

	if (sayso.installed) { // app already installed
		setCookie('sayso-installing', null, -10);
		return;
	}

	// sanity check.. since the app loads LATE in the DOM,
	// we setup a timer now to fire when loaded and set the *installed* cookie
	// (which the above condition checks and returns)
	new jsLoadTimer().setMaxCount(1000).start(
		function () {
			return window.sayso.starbar.loaded;
		},
		function () {
			// Starbar IS installed
			setCookie('sayso-installing', null, -10);
			setCookie('sayso-installed', 1, 30);
		}
	);

	if (getCookie('sayso-installing')) {
		setCookie('sayso-installing', null, -10);
		// Chrome workflow requires a refresh, Firefox requires one on tabs that are early in stack
		if ((navigator.userAgent.match('Firefox') || navigator.userAgent.match('Chrome')) &&
			confirm('Click here to finish installing the app!')) {
			// reload page WITHOUT the install param
			location.href = location.protocol + '//' + location.host + location.pathname;
		}
		return;
	}

	// url param exists, set the cookie
	if (installParam) {
		function getInternetExplorerVersion() {
			var rv = -1; // Return value assumes failure.
			if (navigator.appName == 'Microsoft Internet Explorer') {
				var ua = navigator.userAgent;
				var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
				if (re.exec(ua) != null)
					rv = parseFloat(RegExp.$1);
			}
			return rv;
		}

		var ieVersion = getInternetExplorerVersion();
		var appVersion = '';

		if (ieVersion > -1 && ieVersion < 8) appVersion = ' ' + ieVersion;

		if (
			(!navigator.userAgent.match('Mozilla.*Gecko.*Firefox') && !navigator.userAgent.match('Chrome') &&
			!navigator.userAgent.match('MSIE') && !navigator.userAgent.match('AppleWebKit((?!Mobile).)*Safari'))
		   ||
			(ieVersion > -1 && ieVersion < 8)
		) {
			if (navigator.userAgent.match(/Android/i) || navigator.userAgent.match(/webOS/i) || navigator.userAgent.match(/iPhone/i) || navigator.userAgent.match(/iPod/i) || navigator.userAgent.match(/iPad/i)) {
				alert('Sorry! The Say.So Music Bar isn\'t yet available for mobile browsers. Join us via your computer when you can!');
			} else {
				alert('Sorry, your web browser ('+navigator.appName+appVersion+') doesn\'t support the cool features of the Say.So Music Bar. For an optimal experience, use Chrome (www.google.com/chrome), Firefox (www.getfirefox.com) or Safari (www.apple.com/safari). And we support Internet Explorer 8 and above.');
			}
			return; // unsupported browser
		} else {
			setCookie('sayso-install', installParam, 1);
		}
	}

	if (!installCookie && !installParam) {
		// no cookie, no param
		return;
	} else {

		var loginCookie = getCookie(sayso.client.meta.userLoggedInKey),
			userUniqueId = getCookie(sayso.client.meta.uuidKey);

		if (loginCookie && userUniqueId) {
			sayso.client.uuid = userUniqueId;
			sayso.client.userLoggedIn = true;

			// Pre-Install routine
			// pass uuid and token for this user to sayso
			// and in return set cookies on the client

			var iframe = document.createElement('iframe');
			iframe.src =
				'//' + sayso.baseDomain + '/starbar/remote/pre-install' +
				'?auth_key=' + sayso.client.authKey +
				'&client_name=' + sayso.client.name +
				'&client_uuid=' + sayso.client.uuid +
				'&client_uuid_type=' + sayso.client.uuidType +
				'&client_user_logged_in=' + (sayso.client.userLoggedIn ? 'true' : '') +
				'&install_token=' + getRandomToken() +
				'&install_origination=' + (installParam ? installParam : installCookie);
			iframe.width= '0'; iframe.height = '0';
			iframe.scrolling='no';
			// note 'style' property cannot be set directly. must use it's individual properties instead
			iframe.style.width = '0'; iframe.style.height = '0'; iframe.style.border = 'none'; iframe.style.display = 'none';
			document.getElementsByTagName('body')[0].appendChild(iframe);

			// delete the install cookie
			setCookie('sayso-install', null, -10);
		}

		var div = document.createElement('div');
		div.id = 'sayso-container';
		div.style.display = 'none'; div.style.width = '100%'; div.style.height = '100%'; div.style.position = 'absolute'; div.style.top = '0px';
		document.getElementsByTagName('body')[0].appendChild(div);

		var sayso = window.sayso;

		// jquery

		if (!window.$SQ) {
			var jQueryInclude = document.createElement('script');
			jQueryInclude.src = '//' + sayso.baseDomain + '/js/starbar/jquery-1.6.1.min.js';
			document.getElementById('sayso-container').appendChild(jQueryInclude);
		}

		if (!window.CrossriderAPI) {
			var jsCrossriderInstaller = document.createElement('script');
			jsCrossriderInstaller.src = 'https://crossrider.cotssl.net/javascripts/installer/installer.js';
			document.getElementById('sayso-container').appendChild(jsCrossriderInstaller);
		}

		var jQueryTimer = new jsLoadTimer();
		jQueryTimer.start('typeof window.$SQ === "function"', function () {

			var container = $SQ('#sayso-container'),
				body = document.getElementsByTagName('body')[0];

			// css

			var cssGeneric = document.createElement('link');
			cssGeneric.rel = 'stylesheet';
			cssGeneric.href = '//' + sayso.baseDomain + '/client/' + sayso.client.name + '/css/sayso-onboard.css';
			body.appendChild(cssGeneric);

			var cssColorbox = document.createElement('link');
			cssColorbox.rel = 'stylesheet';
			cssColorbox.href = '//' + sayso.baseDomain + '/client/global/css/colorbox.css';
			body.appendChild(cssColorbox);

			$SQ.CrossriderInstaller = new crossriderInstaller({
				app_id:2787,
				app_name:'Say.So Dev CR'
			});

			// overlay

			$SQ.ajax({
				url : '//' + sayso.baseDomain + '/client/' + sayso.client.name + '/install',
				dataType : 'jsonp',
				success : function (response) {

					setTimeout(function () {
						// overlay
						container.html(response.data.html);
						if ((loginCookie && userUniqueId) || installParam) {
							if (!loginCookie || !userUniqueId) {
								container.show();
								alert('Please log in first to install the app');
								$SQ('#sayso-onboard,#sso_wrapper,#sayso-container').hide();
								// fire login callback
								if (sayso.client.loginCallback) sayso.client.loginCallback();
							} else {
								container.fadeTo('slow', 1);
							}
						}
					}, 1000);
				}
			});
		});
	}

	// functions

	function jsLoadTimer(){function d(){j++<=f?b=setTimeout(g,h):"function"===typeof e&&e()}function g(){try{if("function"===typeof c&&c()||"string"===typeof c&&eval(c)){b&&clearTimeout(b);try{i()}catch(a){sayso.warn(a)}}else d()}catch(e){d()}}var j=0,f=400,h=50,c="",i=null,e=null,b=null;this.setMaxCount=function(a){f=a;return this};this.setInterval=function(a){h=a;return this};this.setLocalReference=function(){return this};this.start=function(a,b,d){c=a;i=b;e=d;g();return this}};

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

	/**
	 * Get a random 64 character token
	 */
	function getRandomToken() {

		var s = [],
			characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		for (var i = 0; i < 64; i++) {
			s[i] = characters.substr(Math.floor(Math.random() * 36), 1);
		}
		return s.join('');
	}
})();
