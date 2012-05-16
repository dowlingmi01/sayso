<?php

class Json extends Zend_Json {

	/**
	* Private array containing the json
	* @var array
	*/
	private $_json = array();

	/**
	* Boolean to indicate if the input JSON is valid or not
	*
	* @var boolean
	*/
	private $_goodJson = false;

	/**
	* Constructor - Reads a JSON from a supplied filename
	*
	* @param string $jsonfile Input filenanme
	* @return Json
	* @author Peter Connolly
	*/
	public function __construct($jsonfile)
	{

		if (file_exists($jsonfile)) {
			// Load the JSON from this file
			$fh = fopen($jsonfile, 'r') or die('JSON01: Could not open file!');
			$data = fread($fh, filesize($jsonfile)) or die('JSON02: Could not read file!');
			// Process JSON file
			$this->setJson($this->decode($data));
			fclose($fh);
			$this->_goodJson = true;
			return $this;
		} else {
			$this->_goodJson = false;
			return null;
		}
	}

	/**
	* Return JSON array
	*
	* @param string $column If set, this subset only is returned
	* @returns array JSON
	* @author Peter Connolly
	*/
	public function getJson($column = null)
	{
		if ($column!=null) {
			return $this->_json[$column];
		} else {
			return $this->_json;
		}
	}

	public function setJson($jsonarray)
	{
		$this->_json = $jsonarray;
	}

	/**
	* Check an array for a key and value
	*
	* Used to check permissions
	* @example $allowed = _checkCMSColumn($json['table'],'permissions','allowedit')
	*  returns true if the array 'permissions' contains a key 'allowedit', false otherwise
	*
	* @param mixed $json
	* @return Boolean - True if the $matchkey is found as the value to $inputarray[$matchvalue]
	* @author Peter Connolly
	*/
	public function checkCMSColumn($matchvalue,$matchkey)
	{
		printf("In CheckCMSColumn, looking for [%s] in [%s]",$matchvalue,$matchkey);
		print_r($this->_json);
		$result = false;

		if (array_key_exists($matchvalue,$this->_json)) {

			foreach ($this->_json[$matchvalue] as $key=>$value) {
				if ($value == $matchkey) {
					$result = true;
				}
			}
		}

		return $result;
	}

	/**
	* return an array of columns matching the required values

    *
	* @param string $matchkey -  Key to be searched
	* @param string $matchvalue - Value to be searched in the array
	* @param boolean $recursive - False - return only column name. True - Return tree from this column below
	* @returns array Array of column names which match the value
	* @author Peter Connolly
	*/
	public function getCMSColumns($matchkey,$matchvalue,$recursive=false)
	{
		$returnarray = array();
		foreach ($this->_json['columns'] as $key=>$value) {
			if (array_key_exists($matchkey,$value)) {
				if (in_array($matchvalue,$value[$matchkey])) {
					if (!$recursive) {
					//	$returnarray[$value['colname']] = $value['colname'];
						$returnarray[] = $value['colname'];
					} else {
				//		$returnarray[$value] = $value;
						$returnarray[] = $value;
					}
				}
			}
		}
		return $returnarray;
	}

	/**
	* return an associated array of columns matching the required values

    *
	* @param string $matchkey -  Key to be searched
	* @param string $matchvalue - Value to be searched in the array
	* @param boolean $recursive - False - return only column name. True - Return tree from this column below
	* @returns array Array of column names which match the value
	* @author Peter Connolly
	*/
	public function getCMSColumnsAssoc($matchkey,$matchvalue,$recursive=false)
	{
		$returnarray = array();
		foreach ($this->_json['columns'] as $key=>$value) {
			if (array_key_exists($matchkey,$value)) {
				if (in_array($matchvalue,$value[$matchkey])) {
					if (!$recursive) {
						$returnarray[$value['colname']] = $value['colname'];
						//$returnarray[] = $value['colname'];
					} else {
						$returnarray[$value] = $value;
						//$returnarray[] = $value;
					}
				}
			}
		}
		return $returnarray;
	}


	/**
	* Search the JSON Columns for a specific value
	*
	* @param string $matchcol Column in the array being searched for
	* @param string $matchkey Key being searched for.
	* #returns string Value of the column, or Null if not found
	* @author Peter Connolly
	*/
	public function getColAttr($matchcol,$matchkey) {

		$returnvalue = null;

			foreach ($this->_json['columns'] as $key=>$value) {

				if ($value['colname'] == $matchcol) {
					if (array_key_exists($matchkey,$value)) {
						$returnvalue = $value[$matchkey];
					}
				}
			}

		return $returnvalue;
	}

	/**
	* Search the JSON Table Definition for a specific value
	*
	* @param string $matchcol Column in the array being searched for
	* @param string $matchkey Key being searched for
	* #returns string Value of the column, or Null if not found
	* @author Peter Connolly
	*/
	public function getTableAttr($matchcol)
	{

		$returnvalue = null;
		if (key_exists($matchcol,$this->_json['table'][0])) {
			return $this->_json['table'][0][$matchcol];
		} else {
			return null;
		}
	}

	/**
	* Get the name of the appropriate model
	*
	* If there is no modelname set in the JSON, use the tablename instead
	*
	* @returns string Modelname
	* @author Peter Connolly
	*/
	public function getModel()
	{
		return ($this->getTableAttr("model")!=null)?$this->getTableAttr("model"):$this->getTableAttr("tablename");
	}

	/**
	* Check for a specified table permission
	*
	* @param mixed $permissionrequested
	* @return Boolean - True if the permission is found on this table]
	* @author Peter Connolly
	*/
	public function checkTablePermission($permissionrequested)
	{

		$permissiongranted = false;
		foreach ($this->_json['table'][0]['permissions'] as $key=>$value) {
			if ($value==$permissionrequested) {
				$permissiongranted = true;
			}
		}

		return $permissiongranted;
	}

	/**
	* Check if the JSON we have opened is that which we expected
	*
	* @param mixed $tablename
	*/
	public function validJson($tablename)
	{
		if (strtolower($this->_json['table'][0]['tablename']) == strtolower($tablename)) {
			return true;
		} else {
			if ((array_key_exists('jsonname',$this->_json['table'][0]) && (strtolower($this->_json['table'][0]['jsonname']) == strtolower($tablename)))) {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	* Check if the imported JSON is valid
	*
	* @author Peter Connolly
	*/
	public function isJsonGood() {
		return $this->_goodJson;
	}

}
