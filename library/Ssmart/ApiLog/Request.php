<?php
	/**
	 * <p>The Api Log object</p>
	 *
	 * @package Ssmart
	 */
	class Ssmart_ApiLog_Request extends Ssmart_ApiLog
	{
		protected $_tableName = 'ssmart_log_request';


		////////////////////////////////////////

		/**
		 * Logs any successful call to the api. Calls that encounter an error
		 * from the api code, endpoint, or other unknown errors are still logged.
		 * recognized failed attempts are not - ie failed authentication
		 *
		 * @param Ssmart_EndpointRequest $request an individual request object
		 */
		public function logApiRequest(Ssmart_EndpointRequest $request)
		{
			$this->_db();

			$this->user_id = property_exists($request->auth->user_data, "user_id") ? $request->auth->user_data->user_id : NULL;
			$this->session_id = property_exists($request->auth->user_data, "session_id") ? $request->auth->user_data->session_id : NULL;
			$ssmart_user_type_id = $this->getUserTypeId($request->auth->user_type);
			$ssmart_endpoint_class_id = $this->getEndpointClassId($request->submitted_parameters->action_class, $ssmart_user_type_id);
			$this->ssmart_endpoint_id = $this->getEndpointId($request->submitted_parameters->action, $ssmart_endpoint_class_id);

			//to not store username/unencrypted password combos
			$paramClone = clone $request->submitted_parameters;
			$parameters = $this->_scrub($paramClone);
			$this->parameters = json_encode($parameters);
			$this->save();
		}
	}