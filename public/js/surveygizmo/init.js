(function () {
	if (!window.$SGQ) return;
	$SGQ.loaded = true;
	var el = document.createElement('div');
	el.id = 'sayso-sgq';
	el.setAttribute('value', JSON.stringify(window.$SGQ));
	document.body.appendChild(el);
})();