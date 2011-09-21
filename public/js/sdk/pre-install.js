// Before providing this to client fix the base domain

/**
 * Pre-install setup. Handles updating install link to correct install for browser
 * as well as injecting an iframe which connects the user agent with sayso servers
 * 
 * - put this on the "landing page" where your user will install the app
 * - this JS must be AFTER the "Client post-login variables" JS
 * - ensure 'sayso-get-app' id is added to Install link e.g. <a id="sayso-get-app">Install</a>
 * 
 * @author davidbjames
 */
(function () {
        
    if (!window.sayso || !window.sayso.client || !window.sayso.client.uuid) return;
    
    var saysoDomain = 'local.sayso.com';
    
    // detect browser and provide appropriate install link
    
    var browserAppUrl = 'http://' + saysoDomain + '/install';
    
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
    
    document.getElementById('sayso-get-app').href = browserAppUrl;

    // Pre-Install routine
    // pass uuid and token for this user to sayso
    // and in return set cookies on the client
    
    var iframe = document.createElement('iframe');
    iframe.src = 'http://' + saysoDomain + '/starbar/remote/pre-install?auth_key=309e34632c2ca9cd5edaf2388f5fa3db&name=' + window.sayso.client.name + '&uuid=' + window.sayso.client.uuid + '&uuid_type=' + window.sayso.client.uuid_type + '&install_token=' + getRandomToken();
    iframe.width= '0'; iframe.height = '0'; 
    iframe.scrolling='0'; iframe.style = 'width: 0; height: 0; border: none; display: none;';
    
    document.getElementsByTagName('body')[0].appendChild(iframe);
    
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