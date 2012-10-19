<?php
/**
 * Actions in this controller are for admin tools/reports meant for internal use (esp. devs)
 */
require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Devadmin_DigController extends Api_GlobalController
{
	// if present and is an array, return as json output instead of showing view
	protected $_jsonArray = null;
	private $_savedIds = array();

	public function postDispatch() {
		if ($this->_jsonArray && is_array($this->_jsonArray) && count($this->_jsonArray)) {
			echo json_encode($this->_jsonArray);
			exit;
		} else {
			echo "Total Failure. :(";
			exit;
		}
	}

	// grabs one or more records and sets _jsonArray
	public function ajaxLoadAction() {
		$this->_validateRequiredParameters(array("record_type", "record_id"));

		$unindexArray = array();

		if (((int) $this->record_id) == -1) $this->record_id = null;

		switch($this->record_type) {
			case "report_cell":
				$sql = "SELECT rc.* FROM report_cell rc ";
				if ($this->single_starbar_id) {
					$sql .= " LEFT JOIN report_cell_user_condition rcuc ON rcuc.report_cell_id = rc.id WHERE rc.category != 'study' AND (rcuc.compare_starbar_id IS NULL OR rcuc.compare_starbar_id = " . $this->single_starbar_id . ")";
				}

				if ($this->record_id) $sql .= " WHERE rc.id = " . ((int) $this->record_id);
				$sql .= " ORDER BY FIELD(rc.category, 'Internal', 'Panel', 'Gender', 'Age Range', 'Marital Status', 'Education', 'Ethnicity', 'Industry', 'Income', 'Parental Status', 'Geography', 'Custom', 'Study'), rc.id ASC";
				$unindexedArray = Db_Pdo::fetchAll($sql);
				$ordinal = 1;
				foreach ($unindexedArray as &$row) {
					if ($this->record_id) { // only load children if we are selecting a specific record
						$conditionsSql = "SELECT * FROM report_cell_user_condition WHERE report_cell_id = " . ((int) $row['id']);
						$conditionRows = Db_Pdo::fetchAll($conditionsSql);

						if ($conditionRows) {
							$row['report_cell_user_condition'] = array();
							foreach($conditionRows as &$conditionRow) {
								$row['report_cell_user_condition'][$conditionRow['id'].""] = $conditionRow;
							}
						}
					} else {
						$row['ordinal'] = ($ordinal++)*10;
					}
					$row['number_of_users'] = (int) $row['number_of_users'];
					$row['label'] = strtoupper($row['category']) . ": " . $row['title'] . " (" . $row['number_of_users'] . " users)";
				}
				break;

			case "survey_question":

				if ($this->record_id) {
					$sql = "SELECT * FROM survey_question";
					$sql .= " WHERE id = " . ((int) $this->record_id);
				} elseif ($this->survey_id) {
					$sql = "SELECT * FROM (";
							$sql .= "SELECT id as true_id, concat(choice_type, '-', id) AS id, CONCAT(UPPER(choice_type), ' CHOICE: ', title) as label, choice_type as type, survey_id, title, ordinal FROM survey_question";
							$sql .= " WHERE survey_id = " . ((int) $this->survey_id);
							$sql .= " AND choice_type != 'None'";
						$sql .= " UNION ";
							$sql .= "SELECT id as true_id, concat(data_type, '-', id) AS id, CONCAT(UPPER(data_type), ': ', title) as label, data_type as type, survey_id, title, ordinal FROM survey_question";
							$sql .= " WHERE survey_id = " . ((int) $this->survey_id);
							$sql .= " AND data_type != 'None'";
					$sql .= ") AS survey_questions";
				} else {
					return;
				}
				$sql .= " ORDER BY ordinal, id";

				$unindexedArray = Db_Pdo::fetchAll($sql);
				if ($this->record_id) { // only load children if we are selecting a specific record
					foreach ($unindexedArray as &$row) {
						$sourceSurveyQuestionId = (int) $row['id'];
						if ($row['piped_from_survey_question_id']) $sourceSurveyQuestionId = (int) $row['piped_from_survey_question_id'];
						$choicesSql = "SELECT *, title as label FROM survey_question_choice WHERE survey_question_id = " . $sourceSurveyQuestionId;
						$choices = Db_Pdo::fetchAll($choicesSql);
						if ($choices) $row['survey_question_choice'] = $choices;
					}
				}
				break;
			case "survey":
				if ($this->record_id) {
					$sql = "SELECT * FROM survey WHERE id = " . ((int) $this->record_id);
				} elseif ($this->starbar_id) {
					$sql = "SELECT s.*, s.title as label FROM survey s INNER JOIN starbar_survey_map ssm ON ssm.survey_id = s.id AND ssm.starbar_id = " . ((int) $this->starbar_id);
				} else {
					return;
				}

				$unindexedArray = Db_Pdo::fetchAll($sql);
				if ($this->record_id) { // only load children if we are selecting a specific record
					foreach ($unindexedArray as &$row) {
						$thisSurveyId = (int) $row['id'];
						$surveyStarbarsSql = "SELECT s.id FROM starbar s INNER JOIN starbar_survey_map ssm ON ssm.starbar_id = s.id AND ssm.survey_id = " . $thisSurveyId;
						$surveyStarbars = Db_Pdo::fetchAll($surveyStarbarsSql);
						if ($surveyStarbars) {
							$row['starbar'] = array();
							foreach ($surveyStarbars as $surveyStarbarRow) {
								$row['starbar'][$surveyStarbarRow['id']] = $surveyStarbarRow;
							}
						}
					}
				}
				break;
			case "study_ad":
				$sql = "SELECT *, CONCAT(existing_ad_tag, ' (', UPPER(type), ' - ID: ', id, ')') AS label FROM study_ad";
				$unindexedArray = Db_Pdo::fetchAll($sql);
				break;
			case "starbar":
				$sql = "SELECT * FROM starbar ";
				if ($this->single_starbar_id) $sql .= " WHERE id = " . $this->single_starbar_id;
				$sql .= " ORDER BY id ASC";
				$unindexedArray = Db_Pdo::fetchAll($sql);

				foreach ($unindexedArray as &$row) {
					$starbarId = (int) $row['id'];
					$surveyTypesSql = "SELECT DISTINCT(s.type), s.type AS id, s.type as label FROM survey s INNER JOIN starbar_survey_map ssm ON ssm.survey_id = s.id AND ssm.starbar_id = " . $starbarId;
					$surveyTypesSql .= " WHERE s.type IN ('survey', 'poll', 'trailer', 'quiz')";
					$surveyTypesSql .= " ORDER BY FIELD(s.type, 'survey', 'poll', 'trailer', 'quiz')";
					$surveyTypes = Db_Pdo::fetchAll($surveyTypesSql);
					if ($surveyTypes) $row['survey_type'] = $surveyTypes;
				}
				break;
			default:
				break;
		}

		$idIndexedArray = array();
		foreach ($unindexedArray as $unindexedRow) {
			$idIndexedArray[$unindexedRow['id']] = $unindexedRow;
		}

		$this->_jsonArray = $idIndexedArray;
	}


	// reprocesses one report_cell (currently not recursive, should be)
	public function ajaxReprocessAction() {
		$this->_validateRequiredParameters(array("report_cell_id"));
		$reportCell = new ReportCell();
		$reportCell->loadData($this->report_cell_id);

		$oldProcessingType = $reportCell->processing_type;
		if ($reportCell->processing_type != "manual") {
			$reportCell->processing_type = "manual";
			$reportCell->save();
		}

		$reportCell->processConditions();

		if ($oldProcessingType != "manual") {
			$reportCell->processing_type = $oldProcessingType;
			$reportCell->save();
		}

		$this->_jsonArray = array("successful" => true);
	}

	// save a node tree to the database
	public function ajaxSaveAction() {
		$this->_validateRequiredParameters(array("top_node"));
		$topNode = $this->top_node;
		$topRecordType = $topNode['node_info']['top_record_type'];
		$topRecordId = $topNode['node_info']['top_record_id'];
		$topRecord = $topNode[$topRecordType][$topRecordId];
		$savedRecord = $this->_saveNode($topRecord);
		$this->_jsonArray = array("saved_id" => $savedRecord->id);
	}

	private function _saveNode($node) {
		$record = null;

		switch ($node['node_info']['type']) {
			case 'report_cell':
				$recordClass = 'ReportCell';
				$fields = array('category', 'processing_type', 'conditions_processed', 'condition_type', 'title');

				$reportCell = $this->_getSavedRecord($recordClass, $node, $fields);
				$oldProcessingType = $reportCell->processing_type;
				if ($reportCell->processing_type != "manual") {
					$reportCell->processing_type = "manual";
					$reportCell->save();
				}

				if (isset($node['report_cell_user_condition']) && count($node['report_cell_user_condition'])) {
					foreach($node['report_cell_user_condition'] as $conditionNode) {
						$this->_saveNode($conditionNode);
					}
				}

				if (!$reportCell->conditions_processed) { // if there are duplicates of a report cell, do not process them again
					$reportCell->processConditions();
				}

				if ($oldProcessingType != "manual") {
					$reportCell->processing_type = $oldProcessingType;
					$reportCell->save();
				}

				$record = $reportCell;
				break;
			case 'report_cell_user_condition':
				$recordClass = 'ReportCell_UserCondition';
				$fields = array('report_cell_id', 'condition_type', 'comparison_type', 'compare_starbar_id', 'compare_report_cell_id', 'compare_survey_id', 'compare_survey_question_id', 'compare_survey_question_choice_id', 'compare_study_ad_id', 'compare_string', 'compare_integer', 'compare_decimal');

				$parentReportCellId = (int) $node['report_cell_id'];
				if ($parentReportCellId <= 0 && isset($this->_savedIds[$parentReportCellId])) $node['report_cell_id'] = $this->_savedIds[$parentReportCellId];
				elseif ($parentReportCellId <= 0) return false;

				$compareReportCellId = (isset($node['compare_report_cell_id']) ? (int) $node['compare_report_cell_id'] : null);
				if ($compareReportCellId && $node['report_cell'][$compareReportCellId]['node_info']['updated_since_loading']) {
					$compareReportCell = $this->_saveNode($node['report_cell'][$compareReportCellId]);
					$node['compare_report_cell_id'] = $compareReportCell->id;
				}

				$reportCellUserCondition = $this->_getSavedRecord($recordClass, $node, $fields);

				$record = $reportCellUserCondition;
				break;
			default:
				break;
		}
		return $record;
	}

	private function _getSavedRecord($recordClass, $node, $fields) {
		$record = new $recordClass();
		$nodeId = ((int) $node['id']);
		if ($nodeId < 0 && isset($this->_savedIds[$nodeId])) {
			$record->loadData($this->_savedIds[$nodeId]);
			return $record; // this record is a duplicate node, and has already been saved in this session
		}
		if ($nodeId > 0) $record->loadData($nodeId);
		if ($node['node_info']['updated_since_loading']) {
			foreach ($fields as $field) {
				if (isset($node[$field]) && $node[$field]) {
					$record[$field] = $node[$field];
				} elseif (isset($record[$field])) {
					unset($record[$field]);
				}
			}
			if ($node['node_info']['type'] == 'report_cell') $record->conditions_processed = 0;
			$record->save();
			if ($nodeId < 0) {
				$this->_savedIds[$nodeId] = $record->id;
			}
		}
		return $record;
	}

}
?>
