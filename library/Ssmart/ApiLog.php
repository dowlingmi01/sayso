<?php
/**
 * <p>The Api Log object</p>
 *
 * @package Ssmart
 */
	class Ssmart_ApiLog extends Record
	{
		protected $_tableName = 'ssmart_log';

		// Other table names - I didn't think these really needed their own classes....
		const SSMART_USER_TYPE_TABLE_NAME = "ssmart_user_types";
		const SSMART_ENDPOINT_TABLE_NAME = "ssmart_endpoints";
		const SSMART_ENDPOINT_CLASS_TABLE_NAME = "ssmart_endpoint_classes";

		/* Not sure why save() won't work if these are declared.
		public $user_id;
		public $session_id;
		public $ssmart_user_type_id;
		public $ssmart_endpoint_id;
		public $ssmart_endpoint_class_id;
		public $parameters;
		public $status;
		*/

		/**
		 * @var _db Because the Db_Pdo class isn't complete enough
		 */
		private $_db;

	////////////////////////////////////////

		/**
		 * Logs any successful call to the api. Calls that encounter an error
		 * from the api code, endpoint, or other unknown errors are still logged.
		 * recognized failed attempts are not - ie failed authentication
		 *
		 * @param int $status Use the constants as defined in Api.php
		 * @param Ssmart_EndpointRequest $request an individual reqquest object
		 */
		public function log($status, Ssmart_EndpointRequest $request)
		{
			$this->_db = $this->_db();

			$this->user_id = property_exists($request->auth->user_data, "user_id") ? $request->auth->user_data->user_id : NULL;
			$this->session_id = property_exists($request->auth->user_data, "session_id") ? $request->auth->user_data->session_id : NULL;
			$this->ssmart_user_type_id = $this->getUserTypeId($request->auth->user_type);
			$this->ssmart_endpoint_class_id = $this->getEndpointClassId($request->submitted_parameters->action_class, $this->ssmart_user_type_id);
			$this->ssmart_endpoint_id = $this->getEndpointId($request->submitted_parameters->action, $this->ssmart_endpoint_class_id);
			$this->parameters = json_encode($request->submitted_parameters);
			$this->status = $status;
			$this->save();
		}

		/**
		 * Sets a database connection
		 *
		 * <p>Using Zend_Db_Adapter_Pdo_Mysql allows for more
		 *  database operations than Db_Pdo library.</p>
		 *
		 * @return Zend_Db_Adapter_Pdo_Mysql
		 * @todo this was copy/pasted from elsewhere. could use a reusable home
		 */
		private function _db()
		{
			if (Zend_Registry::isRegistered('db') && Zend_Registry::get('db') instanceof Zend_Db_Adapter_Pdo_Abstract) {
				$db = Zend_Registry::get('db');
				return $db;
			}
		}

		/**
		 * Gets the id of the user type from the name.
		 * If one isn't found, adds a new one.
		 *
		 * @param string $userType the name of the user type
		 * @return int
		 */
		private function getUserTypeId($userType)
		{
			$userTypeId = $this->_db->fetchOne('SELECT id FROM ' . self::SSMART_USER_TYPE_TABLE_NAME . ' WHERE name = ?', $userType);

			if (!$userTypeId)
			{
				$sql = $this->_db->prepare('INSERT INTO ' . self::SSMART_USER_TYPE_TABLE_NAME . ' (name) VALUES (?)');
				$sql->execute(array($userType));
				return $this->_db->lastInsertId();
			} else
				return $userTypeId;
		}

		/**
		 * Gets the id of the endpoint class from the name
		 * If one isn't found, adds a new one.
		 *
		 * @param string $endpointClass
		 * @param int $userTypeId
		 * @return int
		 */
		private function getEndpointClassId($endpointClass, $userTypeId)
		{
			$endpointClassId = $this->_db->fetchOne('SELECT id FROM ' . self::SSMART_ENDPOINT_CLASS_TABLE_NAME . ' WHERE name = ?', $endpointClass);

			if (!$endpointClassId)
			{
				$sql = $this->_db->prepare('INSERT INTO ' . self::SSMART_ENDPOINT_CLASS_TABLE_NAME . ' (ssmart_user_type_id, name) VALUES (?, ?)');
				$sql->execute(array($userTypeId, $endpointClass));
				return $this->_db->lastInsertId();
			} else
				return $endpointClassId;
		}


		/**
		 * Gets the id of the endpoint class from the name.
		 * If one isn't found, adds a new one.
		 *
		 * @param string $endpoint
		 * @param int $endpointClassId
		 * @return int
		 */
		private function getEndpointId($endpoint, $endpointClassId)
		{
			$endpointId = $this->_db->fetchOne('SELECT id FROM ' . self::SSMART_ENDPOINT_TABLE_NAME . ' WHERE name = ?', $endpoint);
			if (!$endpointId)
			{
				$sql = $this->_db->prepare('INSERT INTO ' . self::SSMART_ENDPOINT_TABLE_NAME . ' (ssmart_endpoint_class_id, name) VALUES (?,?)', $endpointClassId, $endpoint);
				$sql->execute(array($endpointClassId, $endpoint));
				return $this->_db->lastInsertId();
			} else
				return $endpointId;
		}

	}