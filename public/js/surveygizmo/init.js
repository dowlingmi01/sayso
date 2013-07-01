window.location.hash = "frame_id=" + $SGQ.frame_id;

// run code for old app
setTimeout(function () {
	if (!window.$SGQ || !window.sayso) newApp(); // user running new app
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
}, 400);


function newApp() {
	$('body'); // does this work for forcing javascript to wait for jquery to load? is this even needed?

	function LoadScriptsSequentially(scriptUrls, callback)
	{
		if (typeof scriptUrls == 'undefined') throw "Argument Error: URL array is unusable";
		if (scriptUrls.length == 0 && typeof callback == 'function') callback();
		$.getScript(scriptUrls.shift(), function() { LoadScriptsSequentially(scriptUrls, callback); });
	}

	var scriptUrls = [
		'//' + $SGQ.base_domain + '/browserapp/js/src/config.js',
		'//' + $SGQ.base_domain + '/browserapp/js/src/lib/jquery-1.10.1.js',
		'//' + $SGQ.base_domain + '/browserapp/js/src/util.js',
		'//' + $SGQ.base_domain + '/browserapp/js/src/dommsg.js',
		'//' + $SGQ.base_domain + '/browserapp/js/src/iframe/api.js',
		'//' + $SGQ.base_domain + '/browserapp/js/src/iframe/framecomm.js',
		'//' + $SGQ.base_domain + '/browserapp/js/src/iframe/frameapp.js'
	];

	LoadScriptsSequentially(scriptUrls);
};
