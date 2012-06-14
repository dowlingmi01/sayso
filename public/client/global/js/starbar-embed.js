
(function () {
	if (!window.sayso || !window.sayso.client)
		return; // No way to identify the user

	// add trim function if needed
	if(typeof String.prototype.trim !== 'function') {
		String.prototype.trim = function() {
			return this.replace(/^\s+|\s+$/g, '');
		};
	}
		
	var sayso = window.sayso;
	
	var loginCookie = !sayso.client.anonymousUsers && getCookie(sayso.client.meta.userLoggedInKey);
	
	var installParam = getUrlParam('sayso-install'),
		installCookie = getCookie('sayso-install'),
		referrerCookie = getCookie('sayso-referrer');
		
	if( loginCookie )
		sayso.client.userLoggedIn = true;
	
	if( installParam || sayso.snakkleWidget || installCookie && sayso.client.userLoggedIn )
		setTimeout(afterPause, 300);

	function afterPause() { if( !window.$SaySoExtension ) {
		
		var installOrigination = installParam || installCookie;
		
		if (!sayso.baseDomain) {
			sayso.baseDomain = 'app.saysollc.com';
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

		
		var bn = getBrowserNameVersion();
		sayso.bn = bn;

		if ( !bn.isSupported && installParam ) {
			if( bn.isMobile ) {
				alert('Sorry! The ' + sayso.client.appName + ' isn\'t yet available for mobile browsers. Join us via your computer when you can!');
			} else {
				alert('Sorry, your web browser doesn\'t support the cool features of the ' + sayso.client.appName + '. For an optimal experience, use the latest versions of Chrome (www.google.com/chrome), Firefox (www.getfirefox.com) or Safari (www.apple.com/safari). And we support Internet Explorer 8 and above.');
			}
		}
		if( installOrigination && (sayso.client.userLoggedIn || sayso.client.anonymousUsers) ) {
			if (!window.$SQ) {
				var jQueryInclude = document.createElement('script');
				jQueryInclude.src = '//' + sayso.baseDomain + '/js/starbar/jquery-1.7.1.min.js';
				document.getElementsByTagName('body')[0].appendChild(jQueryInclude);
			}
			
			var jQueryTimer = new jsLoadTimer();
			jQueryTimer.start('typeof window.$SQ === "function"', function () {

				if( bn.isSupported ) {
					$SQ('body').append('<div id="sayso-container" style="display: none; width: 100%; height: 100%; position: absolute; top: 0px;">')

					// css
					var cssGeneric = document.createElement('link');
					cssGeneric.rel = 'stylesheet';
					cssGeneric.href = '//' + sayso.baseDomain + '/client/' + sayso.client.name + '/css/sayso-onboard.css';
					document.body.appendChild(cssGeneric);

					var cssColorbox = document.createElement('link');
					cssColorbox.rel = 'stylesheet';
					cssColorbox.href = '//' + sayso.baseDomain + '/client/global/css/colorbox.css';
					document.body.appendChild(cssColorbox);
				}
				
				// overlay
				var clientKeys = {};
				
				if( !sayso.client.anonymousUsers )
					for( var i = 0; i < sayso.client.meta.sendKeys.length; i++ )
						clientKeys[sayso.client.meta.sendKeys[i]] = getCookie(sayso.client.meta.sendKeys[i]);

				var ajaxOpts = {
					url : '//' + sayso.baseDomain + '/starbar/install/' + sayso.client.name,
					dataType : 'jsonp',
					data : {
						client_name : sayso.client.name,
						client_keys : clientKeys,
						install_origination : (installParam ? installParam : installCookie),
						user_agent_supported : bn.isSupported,
						install_url : document.location.href,
						location_token : getLocationCookie(),
						referrer : (installParam ? document.referrer : referrerCookie)
						},
					success : function (response) {
						if( bn.isSupported ) {
							// overlay
							$SQ('#sayso-container').html(response.data.html).fadeTo('slow', 1);
							if( installCookie ) {
								setCookie('sayso-install', null, -10);
								setCookie('sayso-referrer', null, -10);
							}
						}
					}
				};
				$SQ.ajax(ajaxOpts);
			}); //end when jQuery loaded
		// end if userLoggedIn
		} else if( bn.isSupported && installParam ) {
			setCookie('sayso-install', installParam, 1);
			setCookie('sayso-referrer', document.referrer, 1);
			alert('Please log in first to install the Say.So app');
			if (sayso.client.loginCallback)
				sayso.client.loginCallback();
		} else if( bn.isSupported && sayso.snakkleWidget ) {
			window.SaySo = window.SaySo || {};
			SaySo.baseUrl = '//' + window.sayso.baseDomain + '/client/snakkle/';
			SaySo.baseImgUrl = '//s3.amazonaws.com/say.so/media/snakkle/web_app_pics/';
			SaySo.locationCookie = getLocationCookie();
			SaySo.baseDomain = sayso.baseDomain;
			SaySo.getCookie = getCookie;
			SaySo.setCookie = setCookie;
			
			var cssWidget = document.createElement('link');
			cssWidget.rel = 'stylesheet';
			cssWidget.href = SaySo.baseUrl + 'css/widget.css';
			document.body.appendChild(cssWidget);

			$.getScript(SaySo.baseUrl + 'js/widget.js');
		}
	}} // end function afterPause


	
	// functions
	function getLocationCookie() {
		var locationCookie = getCookie( 'sayso-location' );
		
		if( !locationCookie ) {
			locationCookie = getRandomToken();
			setCookie( 'sayso-location', locationCookie, 365 );
		}
		
		return locationCookie;
	}

	function jsLoadTimer(){function d(){j++<=f?b=setTimeout(g,h):"function"===typeof e&&e()}function g(){try{if("function"===typeof c&&c()||"string"===typeof c&&eval(c)){b&&clearTimeout(b);try{i()}catch(a){sayso.warn(a)}}else d()}catch(e){d()}}var j=0,f=400,h=50,c="",i=null,e=null,b=null;this.setMaxCount=function(a){f=a;return this};this.setInterval=function(a){h=a;return this};this.setLocalReference=function(){return this};this.start=function(a,b,d){c=a;i=b;e=d;g();return this}};

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
