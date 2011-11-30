
(function () {

	// add trim function if needed
	if(typeof String.prototype.trim !== 'function') {
		String.prototype.trim = function() {
			return this.replace(/^\s+|\s+$/g, ''); 
		};
	}

    if (!window.sayso) window.sayso = {};
    if (!window.sayso.baseDomain) {
        window.sayso.baseDomain = 'app.saysollc.com';
        window.sayso.environment = 'PROD';
    }
    
    var sayso = window.sayso;
     
    var installParam = getUrlParam('sayso-install'),
        installCookie = getCookie('sayso-install');
    
    if (typeof window.KOBJ === 'object' && sayso.starbar.kynetxAppId) { // app already installed
        setCookie('sayso-installing', null, -10);
        return;
    }
    
    if (getCookie('sayso-installing')) {
        setCookie('sayso-installing', null, -10);
        // Chrome workflow requires a refresh, Firefox requires one on tabs that are early in stack
        if ((navigator.userAgent.match('Firefox') || navigator.userAgent.match('Chrome')) && 
            confirm('Click here to finish installing the app!')) { 
            window.location.reload(); 
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
	    }
        setCookie('sayso-install', installParam, 1);
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
            document.getElementsByTagName('body')[0].appendChild(jQueryInclude);
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
    
            // overlay
            
            $SQ.ajax({
                url : '//' + sayso.baseDomain + '/client/' + sayso.client.name + '/install',
                dataType : 'jsonp',
                success : function (response) {
    
                    setTimeout(function () {
                        // overlay
                        container.html(response.data.html);
                        if ((loginCookie && userUniqueId) || installParam) {
	                        container.fadeTo('slow', 1, function () {
	                            if (!loginCookie || !userUniqueId) {
                            		alert('Please log in first to install the app');
						            // delete the install cookie
						            setCookie('sayso-install', null, -10);
	                                $SQ('#sayso-onboard,#sso_wrapper').hide();
	                                // fire login callback 
	                                if (sayso.client.loginCallback) sayso.client.loginCallback();
	                            }
	                        });
						}
                    }, 1000);
                }
            });
        });
    }

    // functions
    
    function jsLoadTimer(){function d(){i++<=b&&(c=setTimeout(e,f))}function e(){try{if(eval(g)){c&&clearTimeout(c);try{h()}catch(a){sayso.warn(a)}}else d()}catch(b){d()}}var i=0,b=400,f=50,g="",h=null,c=null;this.setMaxCount=function(a){b=a};this.setInterval=function(a){f=a};this.setLocalReference=function(){};this.start=function(a,b){g=a;h=b;e()}};

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
