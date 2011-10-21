
(function () {

    if (!window.sayso) window.sayso = {};
    if (!window.sayso.baseDomain) {
        window.sayso.baseDomain = 'app.saysollc.com';
        window.sayso.environment = 'PROD';
    }
    
    var sayso = window.sayso;
     
    if (navigator.userAgent.match('Firefox') || navigator.userAgent.match('Chrome')) {
        // Firefox/Chrome, proceed..
    } else {
        // IE and Safari not supported at the moment
        return;
    }
    
    var installParam = getUrlParam('sayso-install'),
        installCookie = getCookie('sayso-install');
    
    // already installed
    // commented out. advantage: prevent onboarding from showing
    // if page refreshes (after install) with the sayso-install param in it
    // disadvantage: prevents the user from installing again within the
    // period the cookie is alive (1 day)
//    if (installCookie === 'installed') {
//        if (!installParam) {
//            // install param is no longer present, so it's safe
//            // to remove the cookie. if they come in *again* with
//            // the install param, it's from the email and they
//            // probably want to re-install
//            setCookie('sayso-install', null, -10);
//        }
//        return;
//    }
//    if (!installCookie && (!installParam || installCookie === 'installed')) {
    
    // url param exists, set the cookie
    if (installParam) {
        setCookie('sayso-install', 1, 1);
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
            iframe.src = 'http://' + sayso.baseDomain + '/starbar/remote/pre-install?auth_key=' + sayso.client.authKey + '&client_name=' + sayso.client.name + '&client_uuid=' + sayso.client.uuid + '&client_uuid_type=' + sayso.client.uuidType + '&client_user_logged_in=' + (sayso.client.userLoggedIn ? 'true' : '') + '&install_token=' + getRandomToken();
            iframe.width= '0'; iframe.height = '0'; 
            iframe.scrolling='0';
            // note 'style' property cannot be set directly. must use it's individual properties instead
            iframe.style.width = '0'; iframe.style.height = '0'; iframe.style.border = 'none'; iframe.style.display = 'none';
            document.getElementsByTagName('body')[0].appendChild(iframe);
        }
    
        var div = document.createElement('div');
        div.id = 'sayso-container';
        div.style.display = 'none'; div.style.width = '100%'; div.style.height = '100%'; div.style.position = 'absolute';
        document.getElementsByTagName('body')[0].appendChild(div); 
        
        var sayso = window.sayso;
    
        // jquery
        
        if (!window.$SQ) {
            var jQueryInclude = document.createElement('script');         
            jQueryInclude.src = 'http://' + sayso.baseDomain + '/js/starbar/jquery-1.6.1.min.js';
            document.getElementsByTagName('body')[0].appendChild(jQueryInclude);
        }
        
        var jQueryTimer = new jsLoadTimer();
        jQueryTimer.start('typeof window.$SQ === "function"', function () {
    
            var container = $SQ('#sayso-container'), 
                body = document.getElementsByTagName('body')[0];
    
            // css
            
            var cssGeneric = document.createElement('link'); 
            cssGeneric.rel = 'stylesheet';
            cssGeneric.href = 'http://' + sayso.baseDomain + '/client/' + sayso.client.name + '/css/sayso-onboard.css';
            body.appendChild(cssGeneric);
            
            var cssColorbox = document.createElement('link'); 
            cssColorbox.rel = 'stylesheet';
            cssColorbox.href = 'http://' + sayso.baseDomain + '/client/global/css/colorbox.css';
            body.appendChild(cssColorbox);
    
            // overlay
            
            $SQ.ajax({
                url : 'http://' + sayso.baseDomain + '/client/' + sayso.client.name + '/install',
                dataType : 'jsonp',
                success : function (response) {
    
                    setTimeout(function () {
                        // overlay
                        container.html(response.data.html);
                        container.fadeIn('slow');
                    }, 1000);
                }
            });
        });
    }

    // functions
    
    String.prototype.trim = function() {
        return this.replace(/^\s+|\s+$/g,'');
    };
    
    function jsLoadTimer(){function c(){if(i++>g)return clearTimeout(a),false;else a=setTimeout(d,h)}function d(){try{if(eval(e))a&&clearTimeout(a),f();else return c()}catch(b){return c()}}var i=0,g=400,h=50,e="",f=null,a=null;this.setMaxCount=function(b){g=b};this.setInterval=function(b){h=b};this.setLocalReference=function(){};this.start=function(b,a){e=b;f=a;try{eval(e)?f():d()}catch(c){d()}}};

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
