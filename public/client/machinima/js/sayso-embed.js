(function() {
	if (!window.sayso) window.sayso = {};

	if (!window.sayso.client) {
		window.sayso.client = {
			name : 'machinima',
			userLoggedIn : false,
			appName : 'Machinima Recon',
			anonymousUsers : true,
			loginCallback : null
		};
	}
	if (!window.sayso.baseDomain)
		window.sayso.baseDomain = 'app.saysollc.com';

	var embed = document.createElement('script');
	embed.src = '//' + window.sayso.baseDomain + '/client/global/js/starbar-embed.js';
	document.getElementsByTagName('body')[0].appendChild(embed);
})();
