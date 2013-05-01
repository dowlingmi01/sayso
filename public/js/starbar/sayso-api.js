	/**
	 * Sets a function to create the Api object to the sayso object.
	 * @param {String} user_id
	 * @param {String} user_key
	 * @param {String} user_type
	 * @returns {Boolean|void}
	 */
	sayso.Api = function (user_id, user_key, user_type){
		if (!user_id || !user_key)
			return false;
		this.init(user_id, user_key);
		if (user_type)
			this.params.user_type = user_type;
	};

	sayso.Api.prototype = {
		/**
		 * Init for the Api object.
		 * @param {int} user_id
		 * @param {int} user_key
		 */
		init: function(user_id, user_key) {
			this.params = {};
			this.params.user_id = user_id;
			this.params.user_key = user_key;
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
		 * @param {function} cb Succss callback function
		 * @param {function} fb Fail callback function
		 */
		sendRequests: function (cb, fb) {
			var data = {};
			for (key in this.params)
			{
				var keyName = key;
				data[keyName] = this.params[key];
			}
			data["requests"] = this.requests.params;

			if (typeof sayso.location.protocol === 'undefined')
			{
				var protocol = "https://";
			} else if (sayso.location.protocol == "http:") {
				var protocol = "http://";
			} else {
				var protocol = "https://";
			}

			if (typeof forge.request.ajax == "function")
			{
				forge.request.ajax({
					dataType: 'json',
					data : {data: data},
					url : protocol + sayso.baseDomain + "/api3",
					success : cb,
					error: fb
				});
			} else {
				var request = $SQ.ajax({
					url: protocol + sayso.baseDomain + "/api3",
					type: "POST",
					data: {data: data},
					dataType: "json"
				});
				request.done(cb(response));
				request.fail(fb);
			}
		},

		/**
		 *Formats a 'quick' request
		 * @param {Object} params
		 * @param {function} cb
		 * @param {function} fb
		 */
		sendRequest: function (params, cb, fb) {
			this.requests.params['default'] = params;
			this.sendRequests(cb, fb);
		}
	};

	/**
	 * A container for the request params and functions.
	 */
	Request = function (){

	};

	Request.prototype = {
		/**
		 * Holds the request params so they are separate from the functions
		 */
		params: {},

		/**
		 * Sets an additional param in the request object.
		 * @param {String} name
		 * @param {String} value
		 * @param {String} requestName
		 */
		setParam: function(name, value, requestName) {
			this.params[requestName][name] = value;
		}
	};