function showErr( errObject ) {
	forge.logging.error( errObject.message );
}
function chopURL( url ) {
	var i ;
	if( i = url.indexOf('?') )
		return url.slice(0, i);
	else if( i = url.indexOf('#') )
		return url.slice(0, i);
	else
		return url;
}
function openFirstRunTab() {
	forge.tabs.open( 'http://' + sayso.baseDomain + '/starbar/install/post-install');
}
function firstRun( firstRunDone ) {
	if( !firstRunDone )
		forge.prefs.set( 'firstRunDone', true );

	if( forge.is.chrome() ) {
		if( !firstRunDone ) {
			chrome.tabs.query( {}, function( sTabs ) {
				for( var i = 0; i < sTabs.length; i++ ) {
					if( sTabs[i].url.match('sayso-installing') ) {
						chrome.tabs.update(sTabs[i].id, {url: chopURL(sTabs[i].url), active: true});
						return;
					}
				}
				openFirstRunTab();
			});
		}
	} else if( forge.is.safari() ) {
		if( !firstRunDone ) {
			var sTabs = safari.application.activeBrowserWindow.tabs;
			for( var i = 0; i < sTabs.length; i++ ) {
				if( sTabs[i].url.match('sayso-installing') ) {
					sTabs[i].url = chopURL(sTabs[i].url);
					sTabs[i].activate();
					return;
				}
			}
			openFirstRunTab();
		}
	} else if( forge.is.firefox() ) {
		var code = '';
		code += "var tabs = require('tabs'); \n";
		code += "false; \n";
		code += "for( var i = 0; i < tabs.length; i++ ) \n";
		code += "	if( tabs[i].url.match('sayso-installing') ) { \n";
		code += "		var j; \n";
		code += "		if( j = tabs[i].url.indexOf('?') ) \n";
		code += "			tabs[i].url = tabs[i].url.slice(0, j); \n";
		code += "		else if( j = tabs[i].url.indexOf('#') ) \n";
		code += "			tabs[i].url = tabs[i].slice(0, j); \n";
		code += "		tabs[i].activate(); \n";
		code += "		true; \n";
		code += "		break; \n";
		code += "	} \n";
		forge.firefox.evaluate( code, function( tabFound ) {
			if( !tabFound && !firstRunDone )
				openFirstRunTab();
		} );
	}
}
function getScript( scriptName, callback ) {
	if( !sayso.scripts[scriptName] ) {
		forge.request.get( "http://" + sayso.baseDomain + "/js/" + scriptName + "?_=" + ( new Date() ).getTime()
				, function(content) {
					sayso.scripts[scriptName] = content;
					callback(content);
				}
				, showErr
			);
	} else
		callback( sayso.scripts[scriptName] );
}
sayso.scripts = {};
forge.message.listen("get-script", getScript, showErr);
forge.logging.info("Background script loaded");
forge.prefs.get('firstRunDone', firstRun, showErr);

//@ sourceURL=sayso-background.js
