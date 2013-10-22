<?php
/**
 * Aggregation.php
 */

class Aggregation{

	private $_tables = array(
		"ssmart_log_request" => "ssmart_endpoint_id",
		"ssmart_log_error" => "ssmart_endpoint_id",
	);

	private $_frequencies = array(
		"hourly" => "hour",
		"daily" => "day",
		"weekly" => "week",
		"monthly" => "month",
		"yearly" => "year"
	);

	private $_table_prefix = "aggregation";

	public function aggregate($frequency)
	{
		foreach ($this->_tables as $key => $value)
		{
			$aggregationTableName = $this->_bulildAggregationTableName($key, $frequency);
			$prevFrequency = $this->_getPrevFrequency($frequency);

			if ($frequency == "hourly")
				$sourceTableName = $key;
			else {
				$sourceTableName = $this->_bulildAggregationTableName($key, $prevFrequency);
			}

			//Not sure if it's the best way to handle this, but we'll check for the
			//source table existing, if it doesn't, we'll recurs through this
			//function since it will create and populate dependency tables as needed.
			$sourceTableExists = $this->_checkTable($sourceTableName);
			if (!$sourceTableExists)
				$this->aggregate($prevFrequency);

			$aggreatedColumnName = $this->_tables[$key];
			$frequencyColumnName = $this->_frequencies[$frequency];

			//get the last max id saved
			$lastMaxId = $this->_getLastMaxId($sourceTableName, $frequency);
			if (!$lastMaxId)
				$lastMaxId = 0;

			//get the current max id
			$newMaxId = $this->_getNewMaxId($sourceTableName);

			//check if aggregate table exists
			$aggregationTableExists = $this->_checkTable($aggregationTableName);
			if (!$aggregationTableExists)
				$this->_createAggregationTable($aggregationTableName, $aggreatedColumnName, $frequencyColumnName);

			if (is_int($newMaxId) && $newMaxId > $lastMaxId)
			{
				$timeIdentifier = $this->_frequencies[$frequency] . "(NOW())";
				$sql = "INSERT INTO {$aggregationTableName} (user_id, `count`, `date`, {$aggreatedColumnName}, {$frequencyColumnName})
							SELECT user_id, count(id), CURDATE(), {$aggreatedColumnName}, {$timeIdentifier}
							FROM {$sourceTableName}
							WHERE id >{$lastMaxId} AND id <= {$newMaxId}
							GROUP BY user_id, {$aggreatedColumnName}";
				Db_Pdo::execute($sql);

				//save new max id
				$this->_saveNewMaxId($sourceTableName, $frequency, $newMaxId);
			}
		}
	}

	private function _bulildAggregationTableName($tableName, $frequency)
	{
		if (array_key_exists($frequency, $this->_frequencies))
		{
			$seperator = "_";
			return $this->_table_prefix . $seperator . $frequency . $seperator . $tableName;
		} else {
			//todo: log failure
		}
	}

	private function _getPrevFrequency($currentFrequency)
	{
		if ($currentFrequency == "hourly")
			return FALSE;

		while(key($this->_frequencies) !== $currentFrequency)
			next($this->_frequencies);
		prev($this->_frequencies);
		return key($this->_frequencies);
	}

	private function _getLastMaxId($table, $frequency)
	{
		$uniqueFields = array("source_table_name" => $table, "frequency" => $frequency);
		$lastId = new Aggregation_LastId();
		$lastId->loadDataByUniqueFields($uniqueFields);
		return $lastId->last_id ? (int)$lastId->last_id : FALSE;
	}

	private function _getNewMaxId($table)
	{
		$sql = "SELECT MAX(id) as max_id FROM {$table}";
		$newMaxId = Db_Pdo::fetch($sql);
		return $newMaxId ? (int)$newMaxId["max_id"] : FALSE;
	}

	private function _saveNewMaxId($table, $frequency, $value)
	{
		$uniqueFields = array("source_table_name" => $table, "frequency" => $frequency);
		$newMaxId = new Aggregation_LastId();
		$newMaxId->loadDataByUniqueFields($uniqueFields);
		$newMaxId->last_id = $value;
		$newMaxId->save();
	}

	private function _checkTable($tableName)
	{
		return Db_Pdo::fetch("SHOW TABLES LIKE ?", $tableName);
	}

	private function _createAggregationTable($aggregationTableName, $aggreatedColumnName, $frequencyColumnName)
	{
		Db_Pdo::execute("CREATE TABLE `{$aggregationTableName}` (
									  `id` int(11) NOT NULL AUTO_INCREMENT,
									  `user_id` int(11) NOT NULL,
									  `{$aggreatedColumnName}` int(11) NOT NULL,
									  `count` int(11) NOT NULL,
									  `{$frequencyColumnName}` int(11) NOT NULL,
									  `date` date NOT NULL,
									  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
									  `modified` datetime DEFAULT NULL,
									  PRIMARY KEY (`id`))
									  ");

	}
}