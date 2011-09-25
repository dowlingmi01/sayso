/**
 * Starbar Pre-install Setup. 
 * 
 * Handles updating install link to correct install for browser
 * as well as injecting an iframe which connects the user agent with sayso servers
 * 
 * - this JS must be called after client variables setup (window.sayso.client)
 * - ensure 'sayso-get-app' id is added to install link e.g. <a id="sayso-get-app">Install</a>
 * 
 * @author davidbjames
 */
(function () {
        
    if (!window.sayso || !window.sayso.client || !window.sayso.client.uuid) return;
    
    var sayso = window.sayso;
    
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
    
    document.getElementById('sayso-get-app').href = browserAppUrl;

    // Pre-Install routine
    // pass uuid and token for this user to sayso
    // and in return set cookies on the client
    
    var iframe = document.createElement('iframe');
    iframe.src = 'http://' + sayso.baseDomain + '/starbar/remote/pre-install?auth_key=309e34632c2ca9cd5edaf2388f5fa3db&client_name=' + sayso.client.name + '&client_uuid=' + sayso.client.uuid + '&client_uuid_type=' + sayso.client.uuidType + '&client_user_logged_in=' + (sayso.client.userLoggedIn ? 'true' : '') + '&install_token=' + getRandomToken();
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