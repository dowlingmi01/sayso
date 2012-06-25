<?php

class ReportCell extends Record
{
	protected $_tableName = 'report_cell';

	public function processAllSurveys() {
		if (!$this->id) return;

		// Go through user_conditions to determine which users are in this group first
		$this->processConditions();

		// Delete previous reporting data
		$reportCellSurveys = new ReportCell_SurveyCollection();
		$reportCellSurveys->deleteAllForReportCell($this->id);

		// Grab all surveys and process all of them
		$surveys = new SurveyCollection();
		$surveys->loadAllSurveys();

		foreach ($surveys as $survey) {
			// Allow 3 minutes to process each survey for each cell
			set_time_limit(180);
			$reportCellSurvey = new ReportCell_Survey();
			$reportCellSurvey->report_cell_id = $this->id;
			$reportCellSurvey->survey_id = $survey->id;

			$userArray = $survey->getArrayOfUsersWhoResponded($this->comma_delimited_list_of_users);
			if (count($userArray)) {
				$reportCellSurvey->number_of_responses = count($userArray);
				$reportCellSurvey->comma_delimited_list_of_users = ',' . implode(',', $userArray) . ',';
			}

			$reportCellSurvey->save();

			$reportCellSurvey->process();
		}
	}

	public function processConditions() {
		$commaDelimitedListOfUsers = null;

		$conditionsSql = "";

		if ($this->id > 1) { // The first report cell contains all users (no conditions)
			$reportCellUserConditions = new ReportCell_UserConditionCollection();
			$reportCellUserConditions->loadAllForReportCell($this->id);
			$conditionCounter = 1;

			foreach ($reportCellUserConditions as $reportCellUserCondition) {
				$tableName = "";
				$tableReference = "";
				$conditionSql = "";

				// Set table name (e.g. survey_question_response) and table reference (e.g. sqr1, sqr2, etc.)
				switch ($reportCellUserCondition->condition_type) {
					case "choice":
					case "string":
					case "integer":
					case "decimal":
					case "monetary":
						$tableName = "survey_question_response";
						$tableReference = "sqr" . $conditionCounter;
						break;
					case "starbar":
						$tableName = "starbar_user_map";
						$tableReference = "sum" . $conditionCounter;
						break;
					case "report_cell":
						$tableName = "user";
						$tableReference = "u" . $conditionCounter;
					default:
						break;
				}

				// Set SQL for this condition
				switch ($reportCellUserCondition->condition_type) {
					case "choice":
						switch ($reportCellUserCondition->comparison_type) {
							case "=":
							case "!=":
								$conditionSql = "(" . $tableReference . ".survey_question_choice_id " . $reportCellUserCondition->comparison_type . " " . $reportCellUserCondition->compare_survey_question_choice_id;
								$conditionSql .= " AND " . $tableReference . ".survey_question_id = " . $reportCellUserCondition->compare_survey_question_id;
								$conditionSql .= " AND " . $tableReference . ".data_type = '" . $reportCellUserCondition->condition_type . "')";
								break;
							default:
								break;
						}
						break;
					case "string":
						switch ($reportCellUserCondition->comparison_type) {
							case "=":
							case "!=":
								$conditionSql = "LOWER(" . $tableReference . ".response_string) " . $reportCellUserCondition->comparison_type . " " . strtolower($reportCellUserCondition->compare_string);
								break;
							case "contains":
								$conditionSql = "LOWER(" . $tableReference . ".response_string) LIKE '%" . strtolower($reportCellUserCondition->compare_string) . "%'";
								break;
							case "does not contain":
								$conditionSql = "LOWER(" . $tableReference . ".response_string) NOT LIKE '%" . strtolower($reportCellUserCondition->compare_string) . "%'";
								break;
							default:
								break;
						}
						if ($conditionSql) {
							$conditionSql = "(" . $conditionSql;
							$conditionSql .= " AND " . $tableReference . ".survey_question_id = " . $reportCellUserCondition->compare_survey_question_id;
							$conditionSql .= " AND " . $tableReference . ".data_type = '" . $reportCellUserCondition->condition_type . "')";
						}
						break;
					case "integer":
						switch ($reportCellUserCondition->comparison_type) {
							case "=":
							case "!=":
							case ">":
							case ">=":
							case "<":
							case "<=":
								$conditionSql = $tableReference . ".response_integer " . $reportCellUserCondition->comparison_type . " " . $reportCellUserCondition->compare_integer;
								break;
							default:
								break;
						}
						if ($conditionSql) {
							$conditionSql = "(" . $conditionSql;
							$conditionSql .= " AND " . $tableReference . ".survey_question_id = " . $reportCellUserCondition->compare_survey_question_id;
							$conditionSql .= " AND " . $tableReference . ".data_type = '" . $reportCellUserCondition->condition_type . "')";
						}
						break;
					case "decimal":
					case "monetary":
						switch ($reportCellUserCondition->comparison_type) {
							case "=":
							case "!=":
							case ">":
							case ">=":
							case "<":
							case "<=":
								$conditionSql = $tableReference . ".response_decimal " . $reportCellUserCondition->comparison_type . " " . $reportCellUserCondition->compare_decimal;
								break;
							default:
								break;
						}
						if ($conditionSql) {
							$conditionSql = "(" . $conditionSql;
							$conditionSql .= " AND " . $tableReference . ".survey_question_id = " . $reportCellUserCondition->compare_survey_question_id;
							$conditionSql .= " AND " . $tableReference . ".data_type = '" . $reportCellUserCondition->condition_type . "')";
						}
						break;
					case "starbar":
						switch ($reportCellUserCondition->comparison_type) {
							case "=":
							case "!=":
								$conditionSql = $tableReference . ".starbar_id " . $reportCellUserCondition->comparison_type . " " . $reportCellUserCondition->compare_starbar_id;
								break;
							default:
								break;
						}
						break;
					case "report_cell":
						$compareReportCell = new ReportCell();
						$compareReportCell->loadData($reportCellUserCondition->compare_report_cell_id);
						if ($compareReportCell->id && trimCommas($compareReportCell->comma_delimited_list_of_users)) {
							switch ($reportCellUserCondition->comparison_type) {
								case "=":
									$conditionSql = $tableReference . ".id IN (" . trimCommas($compareReportCell->comma_delimited_list_of_users) . ")";
									break;
								case "!=":
									$conditionSql = $tableReference . ".id NOT IN (" . trimCommas($compareReportCell->comma_delimited_list_of_users) . ")";
									break;
								default:
									break;
							}
						}
						break;
					default:
						break;
				}

				// Append the current condition to the rest of the conditions so far
				if ($conditionSql) {
					// Logic for "and" / "or"
					// For "or", use a UNION between multiple SELECT statements (each statement selects user ids)
					// For "and", use INNER JOINs on a single SELECT statement
					switch ($tableName) {
						case "survey_question_response":
							switch ($this->condition_type) {
								case "or":
									if ($conditionsSql) $conditionsSql .= " UNION ";
									$conditionsSql .= "SELECT sr" . $conditionCounter . ".user_id FROM survey_response sr" . $conditionCounter;
									break;
								case "and":
									$conditionsSql .= " INNER JOIN survey_response sr" . $conditionCounter;
									$conditionsSql .= " ON sr" . $conditionCounter . ".user_id = u.id";
									break;
								default:
									break;
							}
							$conditionsSql .= " INNER JOIN survey_question_response sqr" . $conditionCounter;
							$conditionsSql .= " ON sqr" . $conditionCounter . ".survey_response_id = sr" . $conditionCounter . ".id";
							$conditionsSql .= " AND " . $conditionSql;
							break;
						case "starbar_user_map":
							switch ($this->condition_type) {
								case "or":
									if ($conditionsSql) $conditionsSql .= " UNION ";
									$conditionsSql .= "SELECT sum" . $conditionCounter . ".user_id FROM starbar_user_map sum" . $conditionCounter;
									$conditionsSql .= " WHERE " . $conditionSql;
									break;
								case "and":
									$conditionsSql .= " INNER JOIN starbar_user_map sum" . $conditionCounter;
									$conditionsSql .= " ON sum" . $conditionCounter . ".user_id = u.id";
									$conditionsSql .= " AND " . $conditionSql;
									break;
								default:
									break;
							}
							break;
						case "user":
							switch ($this->condition_type) {
								case "or":
									if ($conditionsSql) $conditionsSql .= " UNION ";
									$conditionsSql .= "SELECT u" . $conditionCounter . ".id FROM user u" . $conditionCounter;
									$conditionsSql .= " WHERE " . $conditionSql;
									break;
								case "and":
									$conditionsSql .= " INNER JOIN user u" . $conditionCounter;
									$conditionsSql .= " ON u" . $conditionCounter . ".id = u.id";
									$conditionsSql .= " AND " . $conditionSql;
									break;
								default:
									break;
							}
							break;
						default:
							break;
					}
				}
				$conditionCounter++;
			}

			// SQL for all conditions created, make final additions and run!
			if ($conditionsSql) {
				switch ($this->condition_type) {
					case "or":
						$sql = $conditionsSql;
						break;
					case "and":
						$sql = "
							SELECT DISTINCT(u.id)
							FROM user u
							" . $conditionsSql . "
						";
						break;
					default:
						break;
				}

				$arrayOfUserIds = Db_Pdo::fetchColumn($sql);
				if (sizeof($arrayOfUserIds)) {
					$this->comma_delimited_list_of_users = ',' . implode(',', $arrayOfUserIds) . ',';
					$this->number_of_users = count($arrayOfUserIds);
					$this->conditions_processed = 1;
					$this->save();
				} else {
					$this->comma_delimited_list_of_users = "";
					$this->number_of_users = 0;
					$this->conditions_processed = 1;
					$this->save();
				}
			}
		} else { // report cell id = 1
			$sql = "SELECT count(id) AS userCount FROM user";
			$results = Db_Pdo::fetch($sql);

			if (isset($results['userCount'])) {
				$this->number_of_users = $results['userCount'];
			}
			$this->comma_delimited_list_of_users = "";
			$this->conditions_processed = 1; // Refers to conditions being processed
			$this->save();
		}
	}

	public function afterSave() {
		// When this cell is updated, force report_cell_survey processing to be run again by resetting the last_processed date
		if ($this->id) {
			$sql = "UPDATE report_cell_survey SET last_processed = '0000-00-00 00:00:00' WHERE report_cell_id = ?";
			Db_Pdo::execute($sql, $this->id);
		}
	}
}

