<?php
	/**
	 * <p>The Api Log object</p>
	 *
	 * @package Ssmart
	 */
	class Ssmart_ApiLog_Error extends Ssmart_ApiLog
	{
		protected $_tableName = 'ssmart_log_error';

		/**
		 * @param array $error
		 * @param Ssmart_EndpointRequest $request
		 * @param $exception
		 */
		public function logApiError(array $error, $request, $exception)
		{
			$this->_db();

			if ($request instanceof Ssmart_EndpointRequest)
			{
				if ($request->auth)
				{
					$this->user_id = property_exists($request->auth->user_data, "user_id") ? $request->auth->user_data->user_id : NULL;
					$this->session_id = property_exists($request->auth->user_data, "session_id") ? $request->auth->user_data->session_id : NULL;
				}
				$ssmart_endpoint_id = $this->getEndpointId($request->submitted_parameters->action, NULL, FALSE);
				$this->ssmart_endpoint_id = $ssmart_endpoint_id ? $ssmart_endpoint_id : NULL;

				//@todo may not need to clone - check this
				$paramClone = clone $request->submitted_parameters;
				$parameters = $this->_scrub($paramClone);
				$this->parameters = json_encode($parameters);

				if ($error[0])
				{
					$this->error_response_name = $error[0]["response_name"] ? $error[0]["response_name"] : NULL;
				}

			} elseif ($request instanceof Ssmart_Request) {
				$this->parameters = json_encode($request);

			} elseif (is_string($request)) {
				$this->parameters = $request;

			}

			if ($exception && $exception instanceof Exception)
			{
				$this->exception_trace = $exception->getTraceAsString();
				$this->exception_file = $exception->getFile();
				$this->exception_line = $exception->getLine();
			}

			if ($error[0])
			{
				$this->error_code = $error[0]["code"];
				$this->error_message = json_encode($error[0]["message"]);
				$this->error_type = $error[0]["type"];
			} else {
				$this->error_code = "unknown";
			}

			$this->save();
		}
	}