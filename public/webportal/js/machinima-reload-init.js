function initializeSaySoPortal(email, signature) {
	$(function(){
		var baseDomain = 'reload.app-qa.saysollc.com';
		$('<iframe>', {
			src: '//' + baseDomain + '/machinimareload.html?machinimareload_email=' + email + '&machinimareload_digest=' + signature,
			allowTransparency: 'true'
		}).css({
			width: '100%',
			height: '100%',
			border: 'none'
		}).appendTo($('<div>').css({
			position: 'fixed',
			top: '55px',
			left: 0,
			right: 0,
			bottom: 0,
			backgroundColor: 'transparent',
			zIndex: 1000
		}).appendTo('body'));
	});
}
