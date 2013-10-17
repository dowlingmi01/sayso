sayso.module.util = (function(global, $, config) {
	function addEventListener( element, eventName, callback ) {
		if( element.addEventListener )
			element.addEventListener( eventName, callback, false );
		else
			element.attachEvent( 'on' + eventName, callback );
	}
	function removeEventListener( element, eventName, callback ) {
		if( element.removeEventListener )
			element.removeEventListener( eventName, callback, false );
		else
			element.detachEvent( 'on' + eventName, callback );
	}
	function urlParams(query) {
		var match,
			pl     = /\+/g,  // Regex for replacing addition symbol with a space
			search = /([^&=]+)=?([^&]*)/g,
			decode = function (s) { return decodeURIComponent(s.replace(pl, " ")); },
			result = {};

		while (match = search.exec(query))
			result[decode(match[1])] = decode(match[2]);

		return result;
	}
	function log() {
		if( config && config.baseDomain === 'app.saysollc.com' ) // suppress logging on production
			return;
		var args = Array.prototype.slice.call(arguments);
		if( global.forge ) {
			if( global.forge.is.chrome() || global.forge.is.safari() ) {
				args.unshift('SaySo:');
				global.console.log.apply(global.console, args);
			} else {
				if( args.length === 1 )
					args = args[0];
				global.forge.logging.log(args);
			}
		} else if( global.console ) {
			global.console.log.apply(global.console, args);
		} else
			global.alert(args[0]);
	}

    $.support.placeholder = (function(){
        var i = document.createElement('input');
        return 'placeholder' in i;
    })();

	function evalInPageContext( arg ) {
		var scriptEl = document.createElement('script');
		if( typeof arg === "function" )
			scriptEl.text = '(' + arg + ')();';
		else
			scriptEl.text = arg;
		document.head.appendChild(scriptEl);
		document.head.removeChild(scriptEl);
	}

	function getTime() {
		return (new Date()).getTime();
	}
	return {
		addEventListener: addEventListener,
		removeEventListener: removeEventListener,
		log: log,
		urlParams: urlParams,
		evalInPageContext: evalInPageContext,
		getTime: getTime
	};
})(this, jQuery, sayso.module.config);
