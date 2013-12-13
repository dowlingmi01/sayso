(function(global, state, browserapp, frameComm) {
	frameComm.install();
	$(function(){
		$(global.document).on('sayso:state-login sayso:state-logout sayso:state-ready sayso:state-starbar', browserapp.initApp);
		if( state.ready )
			browserapp.initApp();
	});
}(this, sayso.module.state, sayso.module.browserapp, sayso.module.frameComm))
;
