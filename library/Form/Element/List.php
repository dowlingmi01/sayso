<?php
/**
* Skeleton for a Select form element
*
* @author Peter Connolly
*/
class Form_Element_List extends Zend_Form_Element_Select {
	/**
	* Indicates action being processed (e.g, edit, add, view, detail)
	*
	* @var mixed
	*/
	protected $_action;

	/**
	* Stores the options for this column
	*
	* @var array
	*/
	protected $_options;

		/**
	* Stores extra form array elements that we might create
	*
	* @var mixed
	*/
	private $_newElements = array();

	/**
	* Notify if we have any hidden elements
	*
	* @return Boolean - true if there is a hidden element, false otherwise
	*/
	public function hasHiddenElement()
	{
		return ($this->_newElements != null)?true:false;
	}

	/**
	* Returns the _newElements array, which contains any form fields added by this class
	*
	* @returns array _newElements
	*/
	public function getExtraElements()
	{
		return $this->_newElements;
	}

	public function init()
	{
		// Set the default title
		$this->setLabel(ucwords(str_replace("_"," ",$this->getName())));

		return parent::init();
	}

	private function _setParams()
	{
		//print_r($this->_options);
		if (array_key_exists("listoptions",$this->_options)) {
			$listoptions = array();

			foreach ($this->_options['listoptions'] as $listkey=>$listvalue) {
				$listoptions[$listvalue] = $listvalue;
			}
			$this->setMultiOptions($listoptions);
		}

	}

	public function setReadOnly()
	{
		$this->setAttrib('disabled', 'disabled');
	}

	private function _getAttribute($attrib)
	{

		if (array_key_exists('attributes',$this->_options)) {
			if (is_array($this->_options['attributes'])) {
			 	return in_array($attrib,$this->_options['attributes']);
			} else {
				return $this->_options['attributes'];
			}
		} else {
			return false;
		}
	}

	private function _setHelp()
	{
		$db = Zend_Registry::get('db');
		// Tooltip help

		if (array_key_exists('help',$this->_options)) {
			$this->setAttrib("title", $this->_options['help']);
		} else {
			// Nothing in the JSON. Check the table definition
			if (array_key_exists('tablename',$this->_options['meta']) && (array_key_exists('colname',$this->_options['meta']))) {
				$sql = sprintf("show full columns from %s where field = '%s'",$this->_options['meta']['tablename'],$this->_options['meta']['colname']);

				$coldetails = $db->fetchRow($sql);
				$this->setAttrib("title", $coldetails['Comment']);
			}
		}
	}

	public function buildElement($action,$optionarray,$currentData=null)
	{
		$this->_action = $action;
		$this->_options = $optionarray;

		// Are we going to display this element?
		if (in_array($action,$this->_options['displaywhen'])) {

			$this->_setParams();
			$this->_setHelp();

			// We're going to display this element. Now look to see what we do with it
			switch ($action) {

				case "add" :
				case "duplicate" :
					break;

				case "detail" :
				case "delete" :
					$this->setReadOnly();
					break;

				case "edit":
					// Is it a writeonly field?
					if ($this->_getAttribute("writeonly")) {
						$this->setReadOnly();
					}
					break;
			}
		}

		return $this;
	}
}
?>