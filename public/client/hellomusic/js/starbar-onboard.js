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
    
    /**
     * Javascript Load Timer
     * @author davidbjames
     */
    function jsLoadTimer(){function d(){window.hasOwnProperty(e)?a():b++>c?a():setTimeout(d,50)}var b=0,c=200,e="",a=null;this.start=function(b,c){e=b;a=c;d()}};

    var jQuery = document.createElement('script'); 
    jQuery.src = 'http://' + sayso.baseDomain + '/js/starbar/jquery-1.6.1.min.js';
    document.getElementsByTagName('body')[0].appendChild(jQuery);
    
    var loadTimer = new jsLoadTimer();
    loadTimer.start('$S', function () {
        
        var elemPage = $S('#sayso-onboard');

        elemPage.height($S(window).height());
        elemPage.width($S(window).width());

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
            
            var loginCookie = getCookie('CHOMPUID'),
                userEmail = getCookie('MyEmail');
        
            
            sayso.client = {
                name : 'hellomusic',
                uuid : '',
                uuidType : 'email',
                userLoggedIn : false
            };
            
            if (loginCookie && userEmail) {
                sayso.client.uuid = userEmail;
                sayso.client.userLoggedIn = true;
            }
                
            window.saysoClientSetup = true;
        }
        
        var loadTimer2 = new jsLoadTimer();
        loadTimer2.start('saysoClientSetup', function () {
            if (sayso.client.userLoggedIn) {
                var preInstall = document.createElement('script'); 
                preInstall.src = 'http://' + sayso.baseDomain + '/js/starbar/pre-install.js';
                document.getElementsByTagName('body')[0].appendChild(preInstall);
            }
        });
    });
})();