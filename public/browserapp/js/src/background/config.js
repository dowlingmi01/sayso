window.sayso = window.sayso || {};
sayso.module = sayso.module || {};
sayso.module.config = (function(global) {
	var baseDomain = (global.sayso.baseDomain) || global.location.host;
	var extVersion = global.sayso.version;
	return {
		baseDomain: baseDomain,
		extVersion: extVersion
	};
})(this)
;
