window.sayso = window.sayso || {};
sayso.module = sayso.module || {};
sayso.module.config = (function(global) {
	var location = global.sayso.location || global.location;
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
		webportal: !!extVersion,
		location: location,
		baseDomain: baseDomain,
		extVersion: extVersion
	};
})(this)
;
