sayso.module.comm = (function(global, $, util) {
	var listeners = {};
	function set( name, value ) {
		global.localStorage.setItem(name, JSON.stringify(value));
	}
	function get( name, callback ) {
		callback( JSON.parse(global.localStorage.getItem(name)) );
	}
	function broadcast( name, data ) {
		global.top.postMessage( JSON.stringify(['sayso-broadcast', {name: name, data: data}]), '*');
	}
	function getReplyCallback( id ) {
		return function( data ) {
			global.top.postMessage( JSON.stringify(['sayso-background-reply', {id: id, data: data}]), '*');
		}
	}
	function handleMessage( event ) {
		try {
			var data = JSON.parse(event.data);
			if( typeof data == 'string' )
				data = JSON.parse(data);
			if( data[0] && data[0] == 'sayso-frontend-request' && data[1] && data[1].name && listeners[data[1].name])
				listeners[data[1].name](data[1].data, getReplyCallback(data[1].id));
		} catch( e ) {
			console.log(e.stack);
		}
	}
	function listen( name, callback ) {
		listeners[name] = callback;
	}
	util.addEventListener(global, 'message', handleMessage);
	return {
		set: set,
		get: get,
		ajax: $.ajax,
		broadcast: broadcast,
		listen: listen
	};
})(this, jQuery, sayso.module.util)
;
