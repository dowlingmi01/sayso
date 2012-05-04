$SQ(function(){

	$SQ.ajaxWithAuth = function (options) {
		var starbar_id = null;
		var user_id = null;
		var user_key = null;

		// The following line is copying $SQ.sayso to global scope (window)
		// Polls and Surveys currently break if we add the keyword var to keep it private
		// TODO: find out why that happens
		sayso = window.$SQ.sayso;

		// Authenticated?
		try
		{
			starbar_id = sayso.starbar.id;
			user_id = sayso.starbar.user.id;
			user_key = sayso.starbar.user.key;
		}
		catch (e) {}

		options.data = $SQ.extend(options.data || {}, {
			starbar_id : starbar_id,
			user_id : user_id,
			user_key : user_key,
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
		handleTweet: function (sharedType, sharedId) {
			$SQ('#sayso-starbar').trigger('frameCommunication', ['handleTweet', {
				shared_type: sharedType,
				shared_id: sharedId
			}]);
		},
		openSurvey: function (surveyId) {
			$SQ('#sayso-starbar').trigger('frameCommunication', ['openSurvey', {
				survey_id: surveyId
			}]);
		},
		hideOverlay: function () {
			$SQ('#sayso-starbar').trigger('frameCommunication', ['hideOverlay']);
		},
		alertMessage: function (alertMessage) {
			$SQ('#sayso-starbar').trigger('frameCommunication', ['alertMessage', {
				alert_message: alertMessage
			}]);
		}
	};

	$SQ.insertCommunicationIframe = function(link, container, width, height, scrolling) {
		// This function inserts the iframe (with x-domain communication enabled!)
		// The id of the container is placed inside the 'ref' attribute at the top of the accordion
		new easyXDM.Rpc({
			local: "https://"+$SQ.sayso.baseDomain+"/html/communicator.html",
			swf: "https://"+$SQ.sayso.baseDomain+"/swf/easyxdm.swf",
			remote: link,
			remoteHelper: "https://"+$SQ.sayso.baseDomain+"/html/communicator.html",
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

		$SQ.sayso.starbar.openFrameContainer = $SQ('#'+container);
		$SQ.sayso.starbar.openFrame = sayso.starbar.openFrameContainer.children('iframe');
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

	$SQ.newWin = false;

	$SQ.openWindow = function (url, name, parameters) {
		var newWin = $SQ.newWin;

		if (newWin && !newWin.closed) {
			newWin.location.href = url;
		} else {
			newWin = window.open(url, 'newWin', parameters);
		}

		if (window.focus) newWin.focus();

		$SQ.newWin = newWin;
		return false;
	}
});
