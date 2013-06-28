(function(global, state, browserapp) {
	$(function(){
		$(global.document).on('sayso:state-login sayso:state-logout sayso:state-ready', browserapp.initApp);
		if( state.ready )
			browserapp.initApp();
	});
}(this, sayso.module.state, sayso.module.browserapp))
;
