(function() {
	window.sayso = window.sayso || {};

	if (!window.sayso.client) {
		window.sayso.client = {
			name : 'snakkle',
			userLoggedIn : false,
			appName : 'Snakkle Say.So App',
			anonymousUsers : true,
			loginCallback : null
		};
	}
	if (!window.sayso.baseDomain)
		window.sayso.baseDomain = location.href.match('staging') ? 'app-staging.saysollc.com' : 'app.saysollc.com';
		
	window.sayso.snakkleWidget = true;
	
	var embed = document.createElement('script');
	embed.src = '//' + window.sayso.baseDomain + '/client/global/js/starbar-embed.js';
	document.getElementsByTagName('body')[0].appendChild(embed);
})();
