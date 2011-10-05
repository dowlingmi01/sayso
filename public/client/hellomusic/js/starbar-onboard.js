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
    if (!window.sayso.baseDomain) window.sayso.baseDomain = 'app-dev.saysollc.com';
    if (!window.sayso.environment) window.sayso.environment = 'DEV';
//    if (!window.sayso.baseDomain) window.sayso.baseDomain = 'local.sayso.com';
//    if (!window.sayso.environment) window.sayso.environment = 'LOCAL';
    
    var sayso = window.sayso;

    if (!window.hasOwnProperty('$SQ')) {
        var jQueryInclude = document.createElement('script'); 
        jQueryInclude.src = 'http://' + sayso.baseDomain + '/js/starbar/jquery-1.6.1.min.js';
        document.getElementsByTagName('body')[0].appendChild(jQueryInclude);
    }
    
    var loadTimer = new jsLoadTimer();
    loadTimer.start('$SQ', function () {
        
        // -------------------------------------------
        // Custom setup 
        
        var elemPage = $SQ('#sayso-onboard');

        elemPage.height($SQ(window).height());
        elemPage.width($SQ(window).width());
        
        $SQ('#sso_wrapper input[type=radio]').attr('checked', false);
        
        $SQ('#sayso-get-app').click(function(e) {
            if (!$SQ('#sso_wrapper input[type=radio]').is(':checked')) {
                e.preventDefault();
                $SQ('span.sso_textError').fadeIn('slow');
            }
        });
        $SQ('#sso_wrapper input[type=radio]').bind('change', function () {
            $SQ('span.sso_textError').fadeOut('slow');
            $SQ('#sayso-get-app').removeClass('sso_theme_button_disabled');
        });
        
        // -------------------------------------------

        String.prototype.trim = function() {
            return this.replace(/^\s+|\s+$/g,'');
        };

        if (!window.saysoClientSetup) {
            
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
            
            // -------------------------------------------
            // Custom setup 
            
            var loginCookie = getCookie('CHOMPUID'),
                userEmail = getCookie('MyEmail');
            
            sayso.client = {
                name : 'hellomusic',
                uuid : '',
                uuidType : 'email',
                userLoggedIn : false
            };
            
            // -------------------------------------------
            
            if (loginCookie && userEmail) {
                sayso.client.uuid = userEmail;
                sayso.client.userLoggedIn = true;
            }
                
            window.saysoClientSetup = true;
        }
        
        var loginTimer = new jsLoadTimer();
        loginTimer.start('saysoClientSetup', function () { 
            if (sayso.client.userLoggedIn) {
                
                // detect browser and provide appropriate install link
                var browserAppUrl = 'http://' + sayso.baseDomain + '/install';
                
                if (navigator.userAgent.match('Firefox')) {
                    browserAppUrl += '/firefox/SaySo-' + sayso.environment + '.xpi';
                } else if (navigator.userAgent.match('Chrome')) {
                    browserAppUrl += '/chrome/SaySo-' + sayso.environment + '.crx';
                } else if (navigator.userAgent.match('MSIE')) {
                    browserAppUrl += '/ie/SaySo-' + sayso.environment + '-Setup.exe';
                } else if (navigator.userAgent.match('Apple')) {
                    browserAppUrl += '/safari/SaySo-' + sayso.environment + '.hmm';
                } else {
                    // Browser is not supported. Must be Firefox, Chrome, Safari or Internet Explorer.
                    return;
                }
                
                $SQ('#sayso-get-app').attr('href', browserAppUrl);

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
            }
        });
        
        var starbarLoad = new jsLoadTimer();
        starbarLoad.start('window.sayso.starbar.loaded', function () { 
            // Starbar already loaded
            $SQ('#sayso-get-app').addClass('sso_theme_button_disabled').text('Installed!').removeAttr('href');
            $SQ('#sso_wrapper input[type=radio]').attr('checked', 'checked');
            $SQ('span.sso_textError').text('');
        });
    });
    
    /**
     * Javascript Load Timer
     * - waits until specified JS symbol comes into existence
     *   (and evaluates to true) and then fires a callback
     * - gives up after 20 seconds
     * 
     * var timer = new jsLoadTimer();
     * timer.start('window.sayso.starbar', function () { // ready, so fire } );
     * 
     * @author davidbjames
     */
    function jsLoadTimer(){function c(){try{if(eval(d)){a&&clearTimeout(a);e();return}}catch(h){}b++>f?clearTimeout(a):a=setTimeout(c,g)}var b=0,f=400,g=50,d="",e=null,a=null;this.start=function(a,b){d=a;e=b;c()}};
    
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
