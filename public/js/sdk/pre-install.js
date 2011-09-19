// Place this Javascript on the Install Page AFTER the client-vars Javascript

// @todo us:        - set base domain 
// @todo customer:  - include client-vars JS before this
//                  - add 'sayso-get-app' id to install link/button

(function () {
        
    if (!window.sayso || !window.sayso.client || !window.sayso.client.uuid) return;
    
    var saysoDomain = 'local.sayso.com';
    
    // detect browser and provide appropriate install link
    
    var browserAppUrl = 'http://' + saysoDomain + '/install';
    
    if (navigator.userAgent.match('Firefox')) {
        browserAppUrl += '/SaySo-Firefox-DEV.xpi';
    } else if (navigator.userAgent.match('Chrome')) {
        browserAppUrl += '/SaySo-Chrome-DEV.crx';
    } else if (navigator.userAgent.match('MSIE')) {
        browserAppUrl += '/SaySo-Internet-Explorer-DEV-Setup.exe';
    } else if (navigator.userAgent.match('Apple')) {
        browserAppUrl += '/SaySo-Safari-DEV.hmm';
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
    iframe.scrolling='0'; iframe.style = 'width: 0; height: 0; border: none;';
    
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