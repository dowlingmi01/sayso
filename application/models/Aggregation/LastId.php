<?php
/**
 * Last_id.php
 */

class Aggregation_LastId extends Record {

	protected $_tableName = "aggregate_last_id";
	protected $_uniqueFields = array("source_table_name" => "", "frequency" => "");


}