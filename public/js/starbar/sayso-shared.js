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
			this.detach();
			this.empty();
		},

		// @todo temporary workaround for http://bugs.jquery.com/ticket/10460
		// remove this function and replace calls to .cleanHtml() with calls to .html() when jquery is fixed!
		cleanHtml: function() {
			return this.html().replace('<a xmlns="http://www.w3.org/1999/xhtml">', '').replace('</a>', '');
		},

    	dataContainer: function (parentIndex) {
        
	        var _container;
	        if (typeof parentIndex === 'number') {
	            // if parent is explicitly set
	            _container = this.parents('[data-id]').eq(parentIndex);
	        } else if (this.attr('data-id').length) {
	            // if the data-id exists on *this* element
	            _container = this;
	        } else {
	            // otherwise default to first parent
	            _container = this.parents('[data-id]').eq(0);
	        }
	        
	        if (!_container.length) {
	            // if none found provide harmless object
	            return {
	                getId : function () { return 0; },
	                setObject : function () { return this; },
	                getObject : function () { return this; },
	                reset : function () {},
	                removeNow : function () {}
	            };
	        }
	        
	        // store off the id
	        var _id = _container.attr('data-id');
	        
	        /**
	         * Get the ID of the object
	         * - this usually corresponds to the record ID
	         * @returns integer
	         */
	        _container.getId = function () {
	            return typeof _id === 'undefined' ? 0 : parseInt(_id);
	        };
	        
	        /**
	         * Attach an object to this data container
	         * @param object|string object
	         */
	        _container.setObject = function (object) {
	            _container.data('object', typeof(object) === 'string' ? object : JSON.stringify(object));
	            return _container;
	        };
	        
	        /**
	         * Get the object from this data container
	         * @returns object
	         */
	        _container.getObject = function () {
	            return JSON.parse(_container.data('object'));
	        };
	        
	        /**
	         * Copy the current data container to another DOM node
	         * @param target
	         * @returns object "data container"
	         */
	        _container.copy = function (target) {
	            if (typeof _container.data('object') !== 'undefined') {
	                target.data('object', _container.data('object'));
	            }
	            target.attr('data-id', _container.getId());
	            return target.dataContainer(); // the new data container
	        };
	        
	        /**
	         * Move the current data container to another DOM node
	         * @param target
	         * @returns object "data container"
	         */
	        _container.move = function (target) {
	            var newContainer = _container.copy(target);
	            _container.reset();
	            return newContainer;
	        };
	        
	        /**
	         * Reset the data container (remove id and object)
	         */
	        _container.reset = function () {
	            _container.removeAttr('data-id');
	            _container.removeData('object');
	            return _container;
	        };
	        
	        /**
	         * Remove the data container completely
	         */
	        _container.removeNow = function () {
	            _container.fadeOut(function() {
	                _container.remove();
	            });
	        };
	        
	        return _container;
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
