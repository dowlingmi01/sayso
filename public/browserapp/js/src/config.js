window.sayso = window.sayso || {};
sayso.module = sayso.module || {};
sayso.module.config = (function(global) {
	var location = global.sayso.location || {
		'hash': global.location.hash,
		'host': global.location.host,
		'hostname': global.location.hostname,
		'href': global.location.href,
		'pathname': global.location.pathname,
		'port': global.location.port,
		'protocol': global.location.protocol,
		'search': global.location.search
	};
	var extVersion = global.sayso.version;
	var baseDomain = global.sayso.baseDomain;
	if( !baseDomain ) {
		if( location.host.indexOf("saysollc.com") !== -1 ) { // testing server
			baseDomain = location.host;
		} else {
			baseDomain = "app.saysollc.com";
		}
	}
	return {
		defaultStarbarId: extVersion ? 0 : 4,
		webportal: !extVersion,
		location: location,
		baseDomain: baseDomain,
		extVersion: extVersion
	};
})(this)
;
