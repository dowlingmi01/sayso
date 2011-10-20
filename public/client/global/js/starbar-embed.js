
(function () {

    // don't do anything unless install parameter exists
    if (!getUrlParam('sayso-install')) return false;

    if (navigator.userAgent.match('Firefox') || navigator.userAgent.match('Chrome')) {
        // Firefox/Chrome, proceed..
    } else {
        // IE and Safari not supported at the moment
        return false;
    }
    
    var div = document.createElement('div');
    div.id = 'sayso-container';
    div.style.display = 'none'; div.style.width = '100%'; div.style.height = '100%'; div.style.position = 'absolute';
    document.getElementsByTagName('body')[0].appendChild(div); 
    
    var sayso = window.sayso;

    // jquery
    
    var jQueryInclude = document.createElement('script');         
    jQueryInclude.src = 'http://' + sayso.baseDomain + '/js/starbar/jquery-1.6.1.min.js';
    document.getElementsByTagName('body')[0].appendChild(jQueryInclude);
    sayso.loading = 'jquery';
    
    var jQueryTimer = new jsLoadTimer();
    jQueryTimer.start('window.$SQ', function () {

        sayso.loading = null;
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

                    if ($SQ('#simulate-login').length) {
                        // login popup
                        setTimeout(function () {
                            $SQ('#simulate-login').show();
                        }, 3000);
                    }
                }, 1000);
            }
        });
    });

    // various useful functions
    
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
})();
