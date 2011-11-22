
(function () {
    
    
    var sayso = window.sayso;

    if (!window.$SQ) {
        var jQueryInclude = document.createElement('script');         
        jQueryInclude.src = 'http://' + sayso.baseDomain + '/js/starbar/jquery-1.6.1.min.js';
        document.getElementsByTagName('body')[0].appendChild(jQueryInclude);
    }
    
    var loadTimer = new jsLoadTimer();
    loadTimer.start('typeof window.$SQ === "function"', function () {
        
        var elemPage = $SQ('#sayso-onboard');
        var elemOverlay = $SQ('#sayso-onboard #sso_wrapper');
				var elemClose = $SQ('#sayso-onboard #sso_wrapper #sso_close');

        elemPage.height($SQ(window).height());
        elemPage.width($SQ(window).width());
        
        $SQ(window).resize(function() {
            elemOverlay.css('left','50%');
            elemOverlay.css('margin-left','-300px');
        });
				
				// close button for overlay
				elemClose.live('click',function(){
					elemPage.hide();
					elemOverlay.hide();
				});
            
        elemOverlay.css('display','block');
        
        $SQ('#sso_wrapper input[type=radio]').attr('checked', false);
        
        // Agree to terms
        
        $SQ('#sso_wrapper input[type=radio]').bind('change', function () {
            $SQ('span.sso_textError').fadeOut('slow');
            if (!sayso.client.userLoggedIn) return;
            $SQ('#sayso-get-app').removeClass('sso_theme_button_disabled');
            if (navigator.userAgent.match('Firefox') || navigator.userAgent.match('Chrome')) {
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
                
                if (navigator.userAgent.match('Chrome')) {
                    // For Chrome users, prompt to reload the page
                    $SQthis = $SQ(this);
                    setTimeout(function(){ 
                    	$SQthis.text(sayso.client.meta.customStartMessage);
                        $SQthis.unbind('click').click(function (e) {
                            e.preventDefault();
                            location.reload();
                        });
                    }, 10000);
                    
                } else {
                    // INSTALLING!
                    $SQ(this).addClass('sso_theme_button_disabled');
                    setCookie('sayso-installing', 1, 1);
                }
            } else {
                e.preventDefault();
                $SQ('span.sso_textError').fadeIn('slow');
            }
        });
        
        if (sayso.client.userLoggedIn) {
        
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
                // $SQ('#sayso-get-app').attr('target', 'com.saysollc.saysolocal');
                // browserAppUrl += '/safari/' + appName + '.safariextz';
                browserAppUrl += '/safari/SaySoExtensionDownload.php?env=' + sayso.environment;
            } else {
                // Browser is not supported. Must be Firefox, Chrome, Safari or Internet Explorer.
                return;
            }
            
            $SQ('#sayso-get-app').attr('href', browserAppUrl);
            
            // if terms already checked, then enable the install button
            if ($SQ('#sso_wrapper input[type=radio]').is(':checked')) {
                $SQ('#sayso-get-app').removeClass('sso_theme_button_disabled');
            }
        }
        
        var starbarLoadTimer = new jsLoadTimer();
        starbarLoadTimer.start('window.sayso.starbar.loaded', function () {
            // starbar loaded. make sure this whole overlay goes away
            $SQ('#sayso-get-app')
                .addClass('sso_theme_button_disabled')
                .text('Installed!')
                .removeAttr('href');
            $SQ('#sayso-container').fadeOut('slow', function () { 
                $SQ('#sayso-container').remove(); 
            });
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
    function jsLoadTimer(){function d(){i++<=b&&(c=setTimeout(e,f))}function e(){try{if(eval(g)){c&&clearTimeout(c);try{h()}catch(a){sayso.warn(a)}}else d()}catch(b){d()}}var i=0,b=400,f=50,g="",h=null,c=null;this.setMaxCount=function(a){b=a};this.setInterval=function(a){f=a};this.setLocalReference=function(){};this.start=function(a,b){g=a;h=b;e()}}
    
    function setCookie(name, value, days) {
        var dateTime = new Date();
        if (days) {
            dateTime.setDate(dateTime.getDate() + days);
        }
        var value = escape(value) + (days ? '; expires=' + dateTime.toUTCString() : '') + '; path=/';
        document.cookie = name + '=' + value;
    }
    
    
})();
