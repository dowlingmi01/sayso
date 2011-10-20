/**
 * Starbar Onboarding (pre-install)
 * 
 * This Javascript handles:
 * - user interface logic
 * - capturing HelloMusic login cookies
 * - setting global variables for HelloMusic
 * - detecting browser and setting correct install URL
 * - setting up installation for this user with SaySo
 *   via injected iframe (which passes the hellomusic
 *   login settings and then returns some cookies)
 */
(function () {
    
    if (!window.sayso) window.sayso = {};
    if (!window.sayso.baseDomain) {
        window.sayso.baseDomain = 'app.saysollc.com';
        window.sayso.environment = 'PROD';
    }
    
    var sayso = window.sayso;

    if (!window.$SQ) {
        sayso.loading = 'jquery';
        var jQueryInclude = document.createElement('script');         
        jQueryInclude.src = 'http://' + sayso.baseDomain + '/js/starbar/jquery-1.6.1.min.js';
        document.getElementsByTagName('body')[0].appendChild(jQueryInclude);
    }
    
    var loadTimer = new jsLoadTimer();
    loadTimer.start('typeof window.$SQ === "function"', function () {
        
        sayso.loading = null;

        var elemPage = $SQ('#sayso-onboard');
        var elemOverlay = $SQ('#sayso-onboard #sso_wrapper');

        elemPage.height($SQ(window).height());
        elemPage.width($SQ(window).width());
        
        $SQ(window).resize(function() {
            elemOverlay.css('left','50%');
            elemOverlay.css('margin-left','-300px');
        });
            
        elemOverlay.css('display','block');
        
        $SQ('#sso_wrapper input[type=radio]').attr('checked', false);
        
        // Agree to terms
        
        $SQ('#sso_wrapper input[type=radio]').bind('change', function () {
            $SQ('span.sso_textError').fadeOut('slow');
            if (!sayso.client.userLoggedIn) return;
            $SQ('#sayso-get-app').removeClass('sso_theme_button_disabled');
            if (navigator.userAgent.match('Chrome')) {
                $SQ('#sayso-install-tip').text('TIP: refresh this page after install.').fadeIn(1500);
            }
        });
        
        // Get the App!
        
        $SQ('#sayso-get-app').click(function(e) {
            if (!sayso.client.userLoggedIn){
                e.preventDefault();
                return;
            }
            if ($SQ('#sso_wrapper input[type=radio]').is(':checked')) {
                $SQ(this).addClass('sso_theme_button_disabled');
                
                // For Chrome users, prompt to reload the page
                
                if (navigator.userAgent.match('Chrome')) {
                    setTimeout(function(){ 
                        $SQ(this).text(sayso.client.meta.customStartMessage);
                        $SQ(this).unbind('click').click(function (e) {
                            e.preventDefault();
                            location.reload();
                        });
                    }, 3000);
                }
            } else {
                e.preventDefault();
                $SQ('span.sso_textError').fadeIn('slow');
            }
        });
        
        sayso.client.preInstallSetup = function () {
            console.log('setting up');
            // detect browser and provide appropriate install link
            var browserAppUrl = 'http://' + sayso.baseDomain + '/install';
            
            var appName = sayso.environment === 'PROD' ? 'Say.So Starbar' : 'SaySo-' + sayso.environment;
            
            if (navigator.userAgent.match('Firefox')) {
                browserAppUrl += '/firefox/' + appName + '.xpi';
            } else if (navigator.userAgent.match('Chrome')) {
                browserAppUrl += '/chrome/' + appName + '.crx';
            } else if (navigator.userAgent.match('MSIE')) {
                browserAppUrl += '/ie/' + appName + '-Setup.exe';
            } else if (navigator.userAgent.match('Apple')) {
                browserAppUrl += '/safari/' + appName + '.hmm';
            } else {
                // Browser is not supported. Must be Firefox, Chrome, Safari or Internet Explorer.
                return;
            }
            
            $SQ('#sayso-get-app').attr('href', browserAppUrl);
            
            // if terms already checked, then enable the install button
            if ($SQ('#sso_wrapper input[type=radio]').is(':checked')) {
                $SQ('#sayso-get-app').removeClass('sso_theme_button_disabled');
            }
            
            // Pre-Install routine
            // pass uuid and token for this user to sayso
            // and in return set cookies on the client
            
            var iframe = document.createElement('iframe');
            iframe.src = 'http://' + sayso.baseDomain + '/starbar/remote/pre-install?auth_key=309e34632c2ca9cd5edaf2388f5fa3db&client_name=' + sayso.client.name + '&client_uuid=' + sayso.client.uuid + '&client_uuid_type=' + sayso.client.uuidType + '&client_user_logged_in=' + (sayso.client.userLoggedIn ? 'true' : '') + '&install_token=' + getRandomToken();
            iframe.width= '0'; iframe.height = '0'; 
            iframe.scrolling='0';
            // note 'style' property cannot be set directly. must use it's individual properties instead
            iframe.style.width = '0'; iframe.style.height = '0'; iframe.style.border = 'none'; iframe.style.display = 'none';
            document.getElementsByTagName('body')[0].appendChild(iframe);
            
            sayso.client.preInstallSetup = null;
        };
        
        String.prototype.trim = function() {
            return this.replace(/^\s+|\s+$/g,'');
        };

        if (!sayso.client.setup) {
            
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
            
            var loginCookie = getCookie(sayso.client.meta.userLoggedInKey),
                userEmail = getCookie(sayso.client.meta.uuidKey);
            
            if (loginCookie && userEmail) {
                sayso.client.uuid = userEmail;
                sayso.client.userLoggedIn = true;
                sayso.client.preInstallSetup();
            }
        }
        
        var starbarLoad = new jsLoadTimer();
        starbarLoad.start('window.sayso.starbar.loaded', function () { 
            // Starbar already loaded
            $SQ('#sayso-get-app').addClass('sso_theme_button_disabled').text('Installed!').removeAttr('href');
            $SQ('#sso_wrapper input[type=radio]').attr('checked', 'checked');
            $SQ('span.sso_textError').text('');
            setTimeout(function () { $SQ('#sso_wrapper').fadeOut('slow'); }, 1000);
            
        });
    });
    
    /**
     * Javascript Load Timer
     * - waits until specified JS symbol/expression evaluates to true and then fires a callback
     * - gives up after 20 seconds
     * 
     * var timer = new jsLoadTimer();
     * timer.start('window.sayso.starbar', function () { // ready, so fire } );
     * 
     * @author davidbjames
     */
    function jsLoadTimer(){function c(){if(i++>g)return clearTimeout(a),false;else a=setTimeout(d,h)}function d(){try{if(eval(e))a&&clearTimeout(a),f();else return c()}catch(b){return c()}}var i=0,g=400,h=50,e="",f=null,a=null;this.setMaxCount=function(b){g=b};this.setInterval=function(b){h=b};this.setLocalReference=function(){};this.start=function(b,a){e=b;f=a;try{eval(e)?f():d()}catch(c){d()}}};
    
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
