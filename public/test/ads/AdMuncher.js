/**
 * 
 */
(function () {

	String.prototype.getParam = function (param) {
		var reg = new RegExp('(?:\\?|&)' + param + '(?:=([^&]*))?(?=&|$)', 'i'),
			result; 
		return (result = this.match(reg)) ? (typeof result[1] === 'undefined' ? '' : decodeURIComponent(result[1])) : undefined;
	};

	// setup
	var http = require('http');
	var querystring = require('querystring');

	// create server
	http.createServer(function (request, response) {
		
		var data = '';
		var params = {};
		
		// listen for when post data arrives and capture it
		request.addListener('data', function(chunk) {
			data += chunk;
		});
		
		// when incoming request is finished, grab the js
		request.addListener('end', function() {
			
			global.window = {};
			global.document = {};
			
			params = querystring.parse(data);
			var output = [];
			for (i = 0; i < 100; i ++) {
				var key = 'js' + i;
				if (!params[key]) break;
				output.push(eval(params[key]));
			}
			
//			eval(params.js);
//			console.log(komliad_base);
			
			var options = {
				host : 'local.sayso.com',
				port : 80,
				path : '/test.js',
				method : 'GET'
			};
			
//			var jsRequest = http.request(options);
//			jsRequest.on('response', function (jsResponse) {
//				var jsData = '';
//				jsResponse.on('data', function (chunk) {
//					jsData += chunk;
//				});
//				jsResponse.on('end', function () {
//					eval(jsData);
//					console.log(pubId);
//				});
//			});
//			jsRequest.end();
//			response.write(eval(params.js));
			
			response.writeHead(200, {'Content-Type': 'text/plain'});
			response.write(JSON.stringify(output))
			response.end();
		});
		
	}).listen(8124);
		
})();

// where i got to: trying to get apricot to work, since we may have to just evaluate entire
// pages or chunks of dom to get this to work correctly... node is complaining about jsdom not found