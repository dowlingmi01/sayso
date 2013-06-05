sayso.module.Api = (function(comm) {
	/**
	 * Sets a function to create the Api object to the sayso object.
	 * @param {String} base_domain
	 * @param {int} user_id
	 * @param {String} user_key
	 * @param {String} user_type
	 * @returns {Boolean|void}
	 */
	var Api = function (base_domain, user_id, user_key, user_type){
		if (!user_id || !user_key)
			return false;
		this.baseDomain = base_domain;
		this.init(user_id, user_key);
		if (user_type)
			this.params.user_type = user_type;
	};

	Api.prototype = {
		/**
		 * Init for the Api object.
		 * @param {int} user_id
		 * @param {String} user_key
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
		 * @param {function} cb Success callback function
		 * @param {function} fb Fail callback function
		 */
		sendRequests: function (cb, fb) {
			var data = {};
			for (var key in this.params)
				data[key] = this.params[key];
			data["requests"] = this.requests.params;

			var protocol = "http://";

			comm.ajax({
				dataType: 'json',
				data : {data: data},
				url : protocol + this.baseDomain + "/ssmart",
				success : cb,
				error: fb
			});
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
	var Request = function (){
		/**
		 * Holds the request params so they are separate from the functions
		 */
		this.params = {}

	};

	Request.prototype = {

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
	return Api;
})(sayso.module.comm)
;
