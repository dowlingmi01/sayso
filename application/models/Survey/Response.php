<?php


class Survey_Response extends Record
{
	protected $_tableName = 'survey_response';

	/**
	 * @var Survey
	 */
	protected $_survey;

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
		$sql = "SELECT user_id FROM survey_response WHERE (status = 'completed' OR status = 'disqualified') AND user_id = ? AND survey_id = ?";
		return !!(Db_Pdo::fetch($sql, $userId, $surveyId));
	}
}
