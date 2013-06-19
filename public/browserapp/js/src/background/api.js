sayso.module.Api = (function(comm) {
	/**
	 * Sets a function to create the Api object to the sayso object.
	 * @param {String} base_domain
	 * @param {int} session_id
	 * @param {String} session_key
	 * @param {String} user_type
	 * @returns {Boolean|void}
	 */
	var Api = function (base_domain, session_id, session_key, user_type){
		this.init(base_domain, session_id, session_key, user_type);
	};

	Api.prototype = {
		/**
		 * Init for the Api object.
		 * @param {int} session_id
		 * @param {String} session_key
		 */
		init: function(base_domain, session_id, session_key, user_type) {
			this.params = {};
			if (session_id) this.params.session_id = session_id;
			if (session_key) this.params.session_key = session_key;
			if ((!session_id || !session_key) && !user_type)
				user_type = 'public';
			if (user_type)
				this.params.user_type = user_type;
			this.baseDomain = base_domain;
			this.requests = new Request();
		},

		/**
		 * Sets the request params to the request object
		 * @param {String} requestName
		 * @param {Object} params
		 * @returns {Request}
		 */
		setRequest: function(requestName, params) {
			this.requests.params[requestName] = params;
			return this.requests;
		},

		/**
		 * Sends the request(s) to the api
		 * @param {function} cb Success callback function
		 * @param {function} fb Fail callback function
         * @param {boolean} keepRequests Whether or not to reset the requests after the callback is done, or not.
         *                               Set to true to keep the requests, otherwise they are reset by default
		 */
		sendRequests: function (cb, fb, keepRequests) {
			var data = {};
			for (var key in this.params)
				data[key] = this.params[key];
			data.requests = this.requests.params;

			var protocol = "http://";
            var requestsToReset = (keepRequests ? false : this.requests);

			if (typeof fb == "undefined") {
				fb = function(e) {
					console.log('API Error(s):');
					console.log(e);
				}
			}

			comm.ajax({
				dataType: 'json',
				data : {data: data},
				url : protocol + this.baseDomain + "/ssmart",
				success : cb,
				error: fb,
                complete: function() { if (requestsToReset) requestsToReset.reset(); } // this happens after success or error
			});
		},

		/**
		 *Formats a 'quick' request. This function always resets the requests before being called,
         *   and optionally resets the request after as well, based on the keepRequests parameter
		 * @param {Object} params
		 * @param {function} cb
		 * @param {function} fb
         * @param {boolean} keepRequests Whether or not to reset the requests object after the callback is done, or not.
         *                               Set to true to keep the requests, otherwise they are reset by default
		 */
		sendRequest: function (params, cb, fb, keepRequests) {
            this.requests.reset();
			this.setRequest('default', params);
			this.sendRequests(cb, fb, keepRequests);
		}
	};

	/**
	 * A container for the request params and functions.
	 */
	var Request = function (){
		/**
		 * Holds the request params so they are separate from the functions
		 */
		this.params = {}

	};

	Request.prototype = {
		/**
		 * Holds the request params so they are separate from the functions
		 */
		params: {},

		/**
		 * Sets an additional param in the request object.
         * @param {String} requestName
		 * @param {String} name
		 * @param {String} value
		 */
		setParam: function(requestName, name, value) {
			if (!requestName in this.params)
				this.params[requestName] = {};
			this.params[requestName][name] = value;
		},

		reset: function() {
			this.params = {};
		}
	};
	return Api;
})(sayso.module.comm)
;
