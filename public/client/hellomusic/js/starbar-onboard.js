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
    if (!window.sayso.baseDomain) window.sayso.baseDomain = 'local.sayso.com';
    
    var sayso = window.sayso;

    var jQueryInclude = document.createElement('script'); 
    jQueryInclude.src = 'http://' + sayso.baseDomain + '/js/starbar/jquery-1.6.1.min.js';
    document.getElementsByTagName('body')[0].appendChild(jQueryInclude);
    
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
                    browserAppUrl += '/firefox/SaySo-DEV.xpi';
                } else if (navigator.userAgent.match('Chrome')) {
                    browserAppUrl += '/chrome/SaySo-DEV.crx';
                } else if (navigator.userAgent.match('MSIE')) {
                    browserAppUrl += '/ie/SaySo-DEV-Setup.exe';
                } else if (navigator.userAgent.match('Apple')) {
                    browserAppUrl += '/safari/SaySo-DEV.hmm';
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
                iframe.scrolling='0'; iframe.style = 'width: 0; height: 0; border: none; display: none;';
                
                document.getElementsByTagName('body')[0].appendChild(iframe);
                
                
            }
        });
    });
    
    /**
     * Javascript Load Timer
     * @author davidbjames
     */
    function jsLoadTimer(){function d(){window.hasOwnProperty(e)?a():b++>c?a():setTimeout(d,50)}var b=0,c=200,e="",a=null;this.start=function(b,c){e=b;a=c;d()}};
    
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