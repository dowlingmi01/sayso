window.location.hash = "frame_id=" + $SGQ.frame_id;

$(function() {
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
});