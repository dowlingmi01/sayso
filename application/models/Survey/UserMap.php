<?php


class Survey_UserMap extends Record
{
	protected $_tableName = 'survey_user_map';
	
	protected $_idKey = null;
	
	protected $_uniqueFields = array('survey_id' => 0, 'user_id' => 0);
	
	/**
	 * @var Survey
	 */
	protected $_survey;
	
	public function init() {
		parent::init();
		// make sure zend db knows this table does not have an id col
		$this->setZendDbTableOptions(array(Zend_Db_Table_Abstract::PRIMARY => NULL));
	}
	
	public function setSurvey (Survey $survey) {
		$this->_survey = $survey;
	}
	
	/**
	 * @return Survey 
	 */
	public function getSurvey() {
		return $this->_survey;
	}
	
	// @todo if you want to return this to the client (e.g. as JSON)
	// then complete the following two methods. Probably also in
	// the Survey class too 
	// SEE User class for examples
	// public function exportData()
	// public function exportProperties($parentObject = null)
	
	function checkIfUserHasCompletedSurvey($userId, $surveyId) {
		$sql = "SELECT user_id FROM survey_user_map WHERE (status = 'completed' OR status = 'disqualified') AND user_id = ? AND survey_id = ?";
		return !!(Db_Pdo::fetch($sql, $userId, $surveyId));
	}
}
