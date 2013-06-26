sayso.module.util = (function($) {
	function addEventListener( element, eventName, callback ) {
		if( element.addEventListener )
			element.addEventListener( eventName, callback, false );
		else
			element.attachEvent( 'on' + eventName, callback );
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
		addEventListener: addEventListener
	};
})(jQuery);
