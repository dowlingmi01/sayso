
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
	
	var loginCookie = getCookie(sayso.client.meta.userLoggedInKey);
	
	var installParam = getUrlParam('sayso-install'),
		installCookie = getCookie('sayso-install'),
		referrerCookie = getCookie('sayso-referrer');
		
	if( loginCookie )
		sayso.client.userLoggedIn = true;
	
	if( installParam || installCookie && sayso.client.userLoggedIn )
		setTimeout(afterPause, 10);

	function afterPause() { if( !window.$SaySoExtension ) {
		if (!sayso.baseDomain) {
			sayso.baseDomain = 'app.saysollc.com';
		}

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
		
		var browserSupported = true;
		
		var ieVersion = getInternetExplorerVersion();
		var appVersion = '';

		if (ieVersion > -1 && ieVersion < 8) appVersion = ' ' + ieVersion;

		if (
			(!navigator.userAgent.match('Mozilla.*Gecko.*Firefox') && !navigator.userAgent.match('Chrome') &&
			!navigator.userAgent.match('MSIE') && !navigator.userAgent.match('AppleWebKit((?!Mobile).)*Safari'))
		   ||
			(ieVersion > -1 && ieVersion < 8)
		) {
			browserSupported = false;
			
			if (navigator.userAgent.match(/Android/i) || navigator.userAgent.match(/webOS/i) || navigator.userAgent.match(/iPhone/i) || navigator.userAgent.match(/iPod/i) || navigator.userAgent.match(/iPad/i)) {
				alert('Sorry! The Say.So Music Bar isn\'t yet available for mobile browsers. Join us via your computer when you can!');
			} else {
				alert('Sorry, your web browser ('+navigator.appName+appVersion+') doesn\'t support the cool features of the Say.So Music Bar. For an optimal experience, use Chrome (www.google.com/chrome), Firefox (www.getfirefox.com) or Safari (www.apple.com/safari). And we support Internet Explorer 8 and above.');
			}
		}
		if( sayso.client.userLoggedIn ) {
			if (!window.$SQ) {
				var jQueryInclude = document.createElement('script');
				jQueryInclude.src = '//' + sayso.baseDomain + '/js/starbar/jquery-1.7.1.min.js';
				document.getElementsByTagName('body')[0].appendChild(jQueryInclude);
			}
			
			var jQueryTimer = new jsLoadTimer();
			jQueryTimer.start('typeof window.$SQ === "function"', function () {

				if( browserSupported ) {
					var div = document.createElement('div');
					div.id = 'sayso-container';
					div.style.display = 'none'; div.style.width = '100%'; div.style.height = '100%'; div.style.position = 'absolute'; div.style.top = '0px';
					document.getElementsByTagName('body')[0].appendChild(div);

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
				}
				
				// overlay
				var clientKeys = {};
				for( var i = 0; i < sayso.client.meta.sendKeys.length; i++ )
					clientKeys[sayso.client.meta.sendKeys[i]] = getCookie(sayso.client.meta.sendKeys[i]);

				var ajaxOpts = {
					url : '//' + sayso.baseDomain + '/starbar/install/' + sayso.client.name,
					dataType : 'jsonp',
					data : {
						client_name : sayso.client.name,
						client_keys : clientKeys,
						install_origination : (installParam ? installParam : installCookie),
						user_agent_supported : browserSupported,
						install_url : document.location.href,
						referrer : (installParam ? document.referrer : referrerCookie),
						},
					success : function (response) {
						if( browserSupported ) {
							setTimeout(function () {
								// overlay
								container.html(response.data.html);
								container.fadeTo('slow', 1);
								if( installCookie ) {
									setCookie('sayso-install', null, -10);
									setCookie('sayso-referrer', null, -10);
								}
							}, 1000);
						}
					}
				};
				$SQ.ajax(ajaxOpts);
			}); //end when jQuery loaded
		// end if userLoggedIn
		} else if( browserSupported && installParam ) {
			setCookie('sayso-install', installParam, 1);
			setCookie('sayso-referrer', document.referrer, 1);
			alert('Please log in first to install the Say.So app');
			if (sayso.client.loginCallback)
				sayso.client.loginCallback();
		}
	}} // end function afterPause


	
	// functions

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
})();
