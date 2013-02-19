<?php
/**
* Skeleton for a Survey List form element - a dropdown list of available surveys
*
* @author Peter Connolly
*/
class Form_Element_Surveylist extends Zend_Form_Element_Select {
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
		$surveytype = (array_key_exists('surveytype',$this->_options)!==false?$this->_options['surveytype']:"survey");
		$sg = new SurveyGizmo();
		$surveylist = $sg->getSurveyList($surveytype);
		// Gives an array of available surveys - id and title. We'll also need the key, but that is derived later.
		$listoptions = array();

		if (!empty($surveylist)) {

			// We have at least one survey to add to the list. Add the default value from the json first.
			$listoptions[0] = (array_key_exists('default',$this->_options)!==false?$this->_options['default']:"--Please Select an SG ".ucfirst($surveytype)."--");

			foreach ($surveylist as $listkey=>$listvalue) {
				$listoptions[$listvalue['external_id']] = $listvalue['title'];
			}

			$this->setMultiOptions($listoptions);

		} else {
			$listoptions['0'] = "No unused ".$surveytype."s found" ;
			$this->setMultiOptions($listoptions);
			$this->setReadOnly();
		}

	}

	public function setReadOnly()
	{
		$this->setAttrib('disabled', 'disabled');
	}

	private function _addClickHandler()
	{
		$script = "
			var txtID = $(this).val();
			var txtTitle = $(this).find('option:selected').text();
			if (txtID==0) {
				$('#title').val('');
				txtTitle = '';
			} else {
			    $('#title').val(txtTitle);

			    /* Convert the title into an external key */
 			    txtTitle = txtTitle.replace(/\./g,' '); /* Replace dots with spaces */
 			    txtTitle = txtTitle.replace(/[^A-Za-z0-9 ]+/g,''); /* Remove non-alphabetic characters */
			    txtTitle = txtTitle.replace(/\s\s+/g,' '); /* Replace multiple spaces with one*/
	  		    txtTitle = txtTitle.replace(/\s/g,'-'); /* Replace spaces with hyphen */
            }

			$('#external_key').val(txtTitle);
			$('#external_id').val(txtID);
		";
		$this->setAttrib('onChange',$script);
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
					$this->_addClickHandler();
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
		$this->setAttrib('size', '0');
		return $this;
	}
}
?>