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
		var m = location.host.match(/(?:.+\.)?(.*\.saysollc.com)/);
		if( m ) { // testing server
			baseDomain = m[1];
		} else {
			baseDomain = "app.saysollc.com";
		}
	}
	return {
		defaultStarbarId: 0,
		webportal: !extVersion,
		location: location,
		baseDomain: baseDomain,
		extVersion: extVersion
	};
})(this)
;
