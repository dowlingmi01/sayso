<?php
/**
 * <p>The Api Log object</p>
 *
 * @package Ssmart
 */
	class Ssmart_ApiLog extends Record
	{

		/**
		 * @var _db Because the Db_Pdo class isn't complete enough
		 */
		protected $_db;

		/**
		 * Add the name of any endpoint that could contain
		 * username/unencrypted password combos to this array
		 *
		 * @var array
		 */
		private $_restrictedEndpointParameterTerms = array(
			"password",
			"login",
			"oauth",
			"token",
			"secret",
			"digest"
		);

		// Other table names - I didn't think these really needed their own classes....
		const SSMART_USER_TYPE_TABLE_NAME = "ssmart_user_type";
		const SSMART_ENDPOINT_TABLE_NAME = "ssmart_endpoint";
		const SSMART_ENDPOINT_CLASS_TABLE_NAME = "ssmart_endpoint_class";

	////////////////////////////////////////


		/**
		 * Sets a database connection
		 *
		 * <p>Using Zend_Db_Adapter_Pdo_Mysql allows for more
		 *  database operations than Db_Pdo library.</p>
		 *
		 * @return Zend_Db_Adapter_Pdo_Mysql
		 * @todo this was copy/pasted from elsewhere. could use a reusable home
		 */
		protected function _db()
		{
			if (Zend_Registry::isRegistered('db') && Zend_Registry::get('db') instanceof Zend_Db_Adapter_Pdo_Abstract) {
				$this->_db = Zend_Registry::get('db');
			}
		}

		/**
		 * Removes the password from stored parameters
		 * Only checks the first level of the param object.
		 *
		 * @param $parameters
		 * @return mixed
		 */
		protected function _scrub($parameters)
		{
			foreach ($parameters as $key => $value)
			{
				foreach ($this->_restrictedEndpointParameterTerms as $term)
				{
					if (strpos($key, $term) !== FALSE)
					{
						$parameters->$key = "xxx";
					}
				}
			}
			return $parameters;
		}

		/**
		 * Gets the id of the user type from the name.
		 * If one isn't found, adds a new one.
		 *
		 * @param string $userType the name of the user type
		 * @param bool $create Whether or not to create a record if it doesn't exist
		 * @return int
		 */
		protected function getUserTypeId($userType, $create = TRUE)
		{
			$userTypeId = $this->_db->fetchOne('SELECT id FROM ' . self::SSMART_USER_TYPE_TABLE_NAME . ' WHERE name = ?', $userType);

			if (!$userTypeId && $create)
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
		 * @param bool $create Whether or not to create a record if it doesn't exist
		 * @return int
		 */
		protected function getEndpointClassId($endpointClass, $userTypeId, $create = TRUE)
		{
			$endpointClassId = $this->_db->fetchOne('SELECT id FROM ' . self::SSMART_ENDPOINT_CLASS_TABLE_NAME . ' WHERE name = ?', $endpointClass);

			if (!$endpointClassId && $create)
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
		 * @param bool $create Whether or not to create a record if it doesn't exist
		 * @return int
		 */
		protected function getEndpointId($endpoint, $endpointClassId, $create = TRUE)
		{
			$endpointId = $this->_db->fetchOne('SELECT id FROM ' . self::SSMART_ENDPOINT_TABLE_NAME . ' WHERE name = ?', $endpoint);
			if (!$endpointId && $create)
			{
				$sql = $this->_db->prepare('INSERT INTO ' . self::SSMART_ENDPOINT_TABLE_NAME . ' (ssmart_endpoint_class_id, name) VALUES (?,?)', $endpointClassId, $endpoint);
				$sql->execute(array($endpointClassId, $endpoint));
				return $this->_db->lastInsertId();
			} else
				return $endpointId;
		}
	}