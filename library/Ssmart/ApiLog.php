<?php
/**
 * <p>The Api Log object</p>
 *
 * @package Ssmart
 */
	class Ssmart_ApiLog extends Record
	{
		protected $_tableName = 'ssmart_log';

		const SSMART_USER_TYPE_TABLE_NAME = "ssmart_user_types";
		const SSMART_ENDPOINT_TABLE_NAME = "ssmart_endpoints";
		const SSMART_ENDPOINT_CLASS_TABLE_NAME = "ssmart_endpoint_classes";

		/*
		public $user_id;
		public $session_id;
		public $ssmart_user_type_id;
		public $ssmart_endpoint_id;
		public $ssmart_endpoint_class_id;
		public $parameters;
		public $status;
		*/

		private $_db;

	////////////////////////////////////////
		/**
		 * Sets a database connection
		 *
		 * <p>Using Zend_Db_Adapter_Pdo_Mysql allows for more
		 *  database operations than Db_Pdo library.</p>
		 *
		 * @return Zend_Db_Adapter_Pdo_Mysql
		 */
		private function _db()
		{
			if (Zend_Registry::isRegistered('db') && Zend_Registry::get('db') instanceof Zend_Db_Adapter_Pdo_Abstract) {
				$db = Zend_Registry::get('db');
				return $db;
			}
		}

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