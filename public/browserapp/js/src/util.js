sayso.module.util = (function($) {
	function addEventListener( element, eventName, callback ) {
		if( element.addEventListener )
			element.addEventListener( eventName, callback, false );
		else
			element.attachEvent( 'on' + eventName, callback );
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

	$.fn.extend({

		htmlForTemplate: function() {
			return this.html().replace(/{{&gt;/g, "{{>");
		}

	});

    $.support.placeholder = (function(){
        var i = document.createElement('input');
        return 'placeholder' in i;
    })();

	return {
		addEventListener: addEventListener,
		urlParams: urlParams
	};
})(jQuery);
