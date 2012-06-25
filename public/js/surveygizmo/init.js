setTimeout(function () {
	if (!window.$SGQ) return;
	$SGQ.loaded = true;
	var el = document.createElement('div');
	el.id = 'sayso-sgq';
	el.setAttribute('value', JSON.stringify(window.$SGQ));
	document.body.appendChild(el);
	if( document.createEvent ) {
		var ev = document.createEvent('Event');
		ev.initEvent('saysoSGQ', false, false);
		document.dispatchEvent(ev);
	} else if( document.createEventObject ) {
		var evObj = document.createEventObject();
		// We use an arbitrary rare event on IE8
		document.fireEvent( 'onafterupdate', evObj );
	}
}, 100);