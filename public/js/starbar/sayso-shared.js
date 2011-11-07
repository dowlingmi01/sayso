/*
 * jQuery doTimeout: Like setTimeout, but better! - v1.0 - 3/3/2010
 * http://benalman.com/projects/jquery-dotimeout-plugin/
 *
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */

(function($){var a={},c="doTimeout",d=Array.prototype.slice;$[c]=function(){return b.apply(window,[0].concat(d.call(arguments)))};$.fn[c]=function(){var f=d.call(arguments),e=b.apply(this,[c+f[0]].concat(f));return typeof f[0]==="number"||typeof f[1]==="number"?this:e};function b(l){var m=this,h,k={},g=l?$.fn:$,n=arguments,i=4,f=n[1],j=n[2],p=n[3];if(typeof f!=="string"){i--;f=l=0;j=n[1];p=n[2]}if(l){h=m.eq(0);h.data(l,k=h.data(l)||{})}else{if(f){k=a[f]||(a[f]={})}}k.id&&clearTimeout(k.id);delete k.id;function e(){if(l){h.removeData(l)}else{if(f){delete a[f]}}}function o(){k.id=setTimeout(function(){k.fn()},j)}if(p){k.fn=function(q){if(typeof p==="string"){p=g[p]}p.apply(m,d.call(n,i))===true&&!q?o():e()};o()}else{if(k.fn){j===undefined?e():k.fn(j===false);return true}else{e()}}}})($SQ);


$SQ.ajaxWithAuth = function (options) {
    var starbar_id = null;
    var auth_key = null;
    var user_id = null;
    var user_key = null;

    sayso = window.sayso;
    
    // Authenticated?
    try
    {
        starbar_id = sayso.starbar.id;
        user_id = sayso.starbar.user.id;
        user_key = sayso.starbar.user.key;
        auth_key = sayso.starbar.authKey;
    }
    catch (e) {}
    
    options.data = $SQ.extend(options.data || {}, {
        starbar_id : starbar_id,
        user_id : user_id,
        user_key : user_key,
        auth_key : auth_key
    });

    if (!options.dataType)
    	options.dataType = 'jsonp';

	options.beforeSend = function(x) {
		if (x && x.overrideMimeType) {
			x.overrideMimeType("application/j-son;charset=UTF-8");
		}
	};
    return $SQ.ajax(options);
};

(function($$SQ){
	$$SQ.fn.extend({
		totalHeight: function() {
			return (
				this.height() +
				eval(this.css('margin-top').replace('px','')) +
				eval(this.css('margin-bottom').replace('px','')) +
				eval(this.css('padding-top').replace('px','')) +
				eval(this.css('padding-bottom').replace('px',''))
			);
		},

		percentWidth: function() {
			return Math.round(this.width() * 100 / this.parent().width());
		},

		annihilate: function() {
			this.attr('id', 'sb_oldElement_'+$SQ.randomString(10));
			this.removeClass();
			this.detach();
			this.empty();
		},

		// @todo temporary workaround for http://bugs.jquery.com/ticket/10460
		// remove this function and replace calls to .cleanHtml() with calls to .html() when jquery is fixed!
		cleanHtml: function() {
			return this.html().replace('<a xmlns="http://www.w3.org/1999/xhtml">', '').replace('</a>', '');
		}
	
	});
})($SQ);

$SQ.frameCommunicationFunctions = {
	loadComplete: function (hideLoadingElem, newFrameHeight) {
		$SQ('#sayso-starbar').trigger('frameCommunication', ['loadComplete', {
			hideLoadingElem: hideLoadingElem,
			newFrameHeight: newFrameHeight
		}]);
	},
	updateGame: function (newGame) {
		$SQ('#sayso-starbar').trigger('frameCommunication', ['updateGame', {
			newGame: newGame
		}]);
	},
	handleTweet: function (shared_type, shared_id) {
		$SQ('#sayso-starbar').trigger('frameCommunication', ['handleTweet', {
			shared_type: shared_type,
			shared_id: shared_id
		}]);
	},
	openSurvey: function (survey_id) {
		$SQ('#sayso-starbar').trigger('frameCommunication', ['openSurvey', {
			survey_id: survey_id
		}]);
	},
	hideOverlay: function (survey_id) {
		$SQ('#sayso-starbar').trigger('frameCommunication', ['hideOverlay']);
	},
	alertMessage: function (alertMessage) {
		$SQ('#sayso-starbar').trigger('frameCommunication', ['alertMessage', {
			alertMessage: alertMessage
		}]);
	}
};

$SQ.insertCommunicationIframe = function(link, container, width, height, scrolling) {
	// This function inserts the iframe (with x-domain communication enabled!)
	// The id of the container is placed inside the 'ref' attribute at the top of the accordion
	new easyXDM.Rpc({
		local: "http://"+sayso.baseDomain+"/html/communicator.html",
		swf: "http://"+sayso.baseDomain+"/swf/easyxdm.swf",
		remote: link,
		remoteHelper: "http://"+sayso.baseDomain+"/html/communicator.html",
        container: container,
        props: {
			scrolling: scrolling,
			style: {
				height: parseInt(height)+"px",
				width: parseInt(width)+"px",
				margin: 0,
				border: 0
			}
		}
	}, {
		// Local functions (i.e. remote procedure calls arrive here)
		local: $SQ.frameCommunicationFunctions
	});

	sayso.starbar.openFrameContainer = $SQ('#'+container);
	sayso.starbar.openFrame = sayso.starbar.openFrameContainer.children('iframe');
}

/*
* This function takes two parameters: integer value for string length and optional 
* boolean value true if you want to include special characters in your generated string.
* From: http://jquery-howto.blogspot.com/2009/10/javascript-jquery-password-generator.html
*/
$SQ.randomString = function (length, special) {
	var iteration = 0;
	var randomString = "";
	var randomNumber;
	if(special == undefined){
		var special = false;
	}
	while(iteration < length){
		randomNumber = (Math.floor((Math.random() * 100)) % 94) + 33;
		if(!special){
			if ((randomNumber >=33) && (randomNumber <=47)) { continue; }
			if ((randomNumber >=58) && (randomNumber <=64)) { continue; }
			if ((randomNumber >=91) && (randomNumber <=96)) { continue; }
			if ((randomNumber >=123) && (randomNumber <=126)) { continue; }
		}
		iteration++;
		randomString += String.fromCharCode(randomNumber);
	}
	return randomString;
}
