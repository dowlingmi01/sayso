$SQ.ajaxWithAuth = function (options) {
    var starbar_id = null;
    var auth_key = null;
    var user_id = null;
    var user_key = null;

    sayso = window.sayso;
    
    // Authenticated?
    try
    {
        starbar_id = sayso.starbar.id;
        user_id = sayso.starbar.user.id;
        user_key = sayso.starbar.user.key;
        auth_key = sayso.starbar.authKey;
    }
    catch (e) {}
    
    if (typeof sayso == "undefined") {
    // setup global "safe" logging functions
    window.sayso.log = _log('log'); 
    window.sayso.warn = _log('warn');
    function _log (type) { // <-- closure here allows re-use for log() and warn()
        return function () {
            if (window.sayso.debug && typeof window.console !== 'undefined' && typeof window.console.log !== 'undefined') {
                var args = Array.prototype.slice.call(arguments);
                if (typeof console.log.apply === 'function') {
                    args.unshift('SaySo:');
                    window.console[type].apply(window.console, args);
                } else {
                    // must be IE
                    if (typeof args[0] !== 'object') {
                        window.console.log(args[0]);
                    }
                }
            }
        }
    };
    
    var sayso = window.sayso;
	}
    
    options.data = $SQ.extend(options.data || {}, {
        starbar_id : starbar_id,
        user_id : user_id,
        user_key : user_key,
        auth_key : auth_key
    });

    if (!options.dataType)
    	options.dataType = 'jsonp';

	options.beforeSend = function(x) {
		if (x && x.overrideMimeType) {
			x.overrideMimeType("application/j-son;charset=UTF-8");
		}
	};
    return $SQ.ajax(options);
};

(function($$SQ){
	$$SQ.fn.extend({
		totalHeight: function() {
			return (
				this.height() +
				eval(this.css('margin-top').replace('px','')) +
				eval(this.css('margin-bottom').replace('px','')) +
				eval(this.css('padding-top').replace('px','')) +
				eval(this.css('padding-bottom').replace('px',''))
			);
		}
	});
})($SQ);

