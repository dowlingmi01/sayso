sayso.module.util = (function() {
	function addEventListener( element, eventName, callback ) {
		if( element.addEventListener )
			element.addEventListener( eventName, callback, false );
		else
			element.attachEvent( 'on' + eventName, callback );
	}
	return {
		addEventListener: addEventListener
	};
})()
;
