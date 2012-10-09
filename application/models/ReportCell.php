<?php

class ReportCell extends Record
{
	const ALL_USERS_REPORT_CELL = 1;

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

			$reportCellSurvey->number_of_responses = $survey->getCountOfUsersInReportCellWhoResponded($this->id);
			$reportCellSurvey->save();

			$reportCellSurvey->process();
		}
	}

	public function processConditions() {
		$commaDelimitedListOfUsers = null;

		$conditionsSql = "";

		if ($this->id == self::ALL_USERS_REPORT_CELL) { // the all-users bucket
			$sql = "SELECT count(id) AS userCount FROM user WHERE type != 'test'";
			$results = Db_Pdo::fetch($sql);
			if (isset($results['userCount'])) {
				$this->number_of_users = $results['userCount'];
			}
			$this->conditions_processed = 1; // Refers to conditions being processed
			$this->save();

		} else { // all other buckets
			$reportCellUserConditions = new ReportCell_UserConditionCollection();
			$reportCellUserConditions->loadAllForReportCell($this->id);
			$conditionCounter = 1;

			foreach ($reportCellUserConditions as $reportCellUserCondition) {
				$tableName = "";
				$tableReference = "";
				$conditionSql = "";

				// Set table name (e.g. survey_question_response) and table reference (e.g. sqr1, sqr2, etc.)
				switch ($reportCellUserCondition->condition_type) {
					case "single":
					case "multiple":
					case "string":
					case "integer":
					case "decimal":
					case "monetary":
						$tableName = "survey_question_response";
						$tableReference = "sqr" . $conditionCounter;
						break;
					case "survey_status":
						$tableName = "survey_response";
						$tableReference = "sr" . $conditionCounter;
						break;
					case "starbar":
						$tableName = "starbar_user_map";
						$tableReference = "sum" . $conditionCounter;
						break;
					case "study_ad":
						$tableName = "study_ad_user_map";
						$tableReference = "saum" . $conditionCounter;
						break;
					case "report_cell":
						switch ($reportCellUserCondition->comparison_type) {
							case "in":
								$tableName = "report_cell_user_map";
								$tableReference = "rcum" . $conditionCounter;
								break;
							case "not in":
								$tableName = "user";
								$tableReference = "u" . $conditionCounter;
								break;
							default:
								break;
						}
						break;
					default:
						break;
				}

				// Set SQL for this condition
				switch ($reportCellUserCondition->condition_type) {
					case "single":
						switch ($reportCellUserCondition->comparison_type) {
							case "=":
							case "!=":
								$conditionSql = "(" . $tableReference . ".survey_question_choice_id " . $reportCellUserCondition->comparison_type . " " . $reportCellUserCondition->compare_survey_question_choice_id;
								$conditionSql .= " AND " . $tableReference . ".survey_question_id = " . $reportCellUserCondition->compare_survey_question_id;
								$conditionSql .= " AND " . $tableReference . ".data_type = 'choice')";
								break;
							default:
								break;
						}
						break;
					case "multiple":
						switch ($reportCellUserCondition->comparison_type) {
							case "in":
							case "not in":
								$conditionSql = "(" . $tableReference . ".survey_question_choice_id " . $reportCellUserCondition->comparison_type . " (" . $reportCellUserCondition->compare_string . ")";
								$conditionSql .= " AND " . $tableReference . ".survey_question_id = " . $reportCellUserCondition->compare_survey_question_id;
								$conditionSql .= " AND " . $tableReference . ".data_type = 'choice')";
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
					case "survey_status":
						switch ($reportCellUserCondition->comparison_type) {
							case "=":
							case "!=":
								$conditionSql = "(" . $tableReference . ".survey_id = " . $reportCellUserCondition->compare_survey_id;
								$conditionSql .= $tableReference . ".status " . $reportCellUserCondition->comparison_type . " '" . $reportCellUserCondition->compare_string . "')";
								break;
							default:
								break;
						}
						break;
					case "starbar":
						switch ($reportCellUserCondition->comparison_type) {
							case "in":
							case "not in":
								$tempComparisonType = str_replace("in", "=", $reportCellUserCondition->comparison_type);
								$tempComparisonType = str_replace("not ", "!", $tempComparisonType);
								$conditionSql = $tableReference . ".starbar_id " . $tempComparisonType . " " . $reportCellUserCondition->compare_starbar_id;
								break;
							default:
								break;
						}
						break;
					case "study_ad":
						switch ($reportCellUserCondition->comparison_type) {
							case "viewed":
								$conditionSql = $tableReference . ".type = 'view'";
								break;
							case "clicked":
								$conditionSql = $tableReference . ".type = 'click'";
								break;
							default:
								break;
						}
						if ($conditionSql) $conditionSql .= " AND " . $tableReference . ".study_ad_id = " . $reportCellUserCondition->compare_study_ad_id;
						break;
					case "report_cell":
						$compareReportCell = new ReportCell();
						$compareReportCell->loadData($reportCellUserCondition->compare_report_cell_id);
						if ($compareReportCell->id) {
							if (!$compareReportCell->conditions_processed) $compareReportCell->processConditions();
							switch ($reportCellUserCondition->comparison_type) {
								case "in": // $tableReference is to report_cell_user_map table
									$conditionSql = $tableReference . ".report_cell_id = " . $reportCellUserCondition->compare_report_cell_id; // == $compareReportCell->id
									break;
								case "not in": // $tableReference is to user table
									$conditionSql = $tableReference . ".id NOT IN ( SELECT user_id FROM report_cell_user_map WHERE report_cell_id = " . $reportCellUserCondition->compare_report_cell_id . " )";
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
						case "study_ad_user_map":
						case "survey_response":
						case "report_cell_user_map":
							switch ($this->condition_type) {
								case "or":
									if ($conditionsSql) $conditionsSql .= " UNION ";
									$conditionsSql .= "SELECT " . $tableReference . ".user_id FROM " . $tableName . " " . $tableReference;
									$conditionsSql .= " WHERE " . $conditionSql;
									break;
								case "and":
									$conditionsSql .= " INNER JOIN " . $tableName . " " . $tableReference;
									$conditionsSql .= " ON " . $tableReference . ".user_id = u.id";
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
									$conditionsSql .= "SELECT " . $tableReference . ".id FROM " . $tableName . " " . $tableReference;
									$conditionsSql .= " WHERE " . $conditionSql;
									break;
								case "and":
									$conditionsSql .= " INNER JOIN " . $tableName . " " . $tableReference;
									$conditionsSql .= " ON " . $tableReference . ".id = u.id";
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
			} // end of foreach on conditions

			// SQL for all conditions created, make final additions and run!
			if ($conditionsSql) {

				switch ($this->condition_type) {
					case "or":
						$sql = "
							SELECT DISTINCT(user_id)
							FROM (" . $conditionsSql . ") AS all_matching_users
							INNER JOIN user u
								ON u.id = all_matching_users.user_id
								AND u.type != 'test'
							ORDER BY user_id
						";
						break;
					case "and":
						$sql = "
							SELECT DISTINCT(u.id)
							FROM user u
							" . $conditionsSql . "
							WHERE u.type != 'test'
							ORDER BY u.id
						";
						break;
					default:
					break;
				}

                $currentUsersInReportCell = Db_Pdo::fetchColumn($sql);
                $this->number_of_users = count($currentUsersInReportCell);

				$sql = "SELECT user_id FROM report_cell_user_map WHERE report_cell_id = " . $this->id;
				$previousUsersInReportCell = Db_Pdo::fetchColumn($sql);
				$previousNumberOfUsers = count($previousUsersInReportCell);


				$reportCellUserListUpdated = false;
				$usersAdded = array();
				$usersRemoved = array();
				$cu = 0;
				$pr = 0;
				while (isset($currentUsersInReportCell[$cu]) || isset($previousUsersInReportCell[$pr])) {
					if ( // user is in current list but not previous list
						(isset($currentUsersInReportCell[$cu]) && !isset($previousUsersInReportCell[$pr])) ||
						($currentUsersInReportCell[$cu] < $previousUsersInReportCell[$pr])
					) {
						$usersAdded[] = $currentUsersInReportCell[$cu];
						$cu++;
					} elseif ( // user is in previous list but not current list
						(!isset($currentUsersInReportCell[$cu]) && isset($previousUsersInReportCell[$pr])) ||
						($currentUsersInReportCell[$cu] > $previousUsersInReportCell[$pr])
					) {
						$usersRemoved[] = $previousUsersInReportCell[$pr];
						$pr++;
					} // user in both lists, go to next item on both lists

					// $currentUsersInReportCell[$cu] == $previousUsersInReportCell[$pr]
					$cu++;
					$pr++;
				}

				if (count($usersAdded)) {
					$reportCellUserListUpdated = true; // this report cell has new users, force reprocessing of surveys for this report_cell
					$valuesToInsert = "";
					$valuesToInsertCount = 0;
					foreach($usersAdded as $userId) {
						if ($valuesToInsert) {
							if ($valuesToInsertCount > 500) { // Insert up to records 500 rows at a time
								Db_Pdo::execute("INSERT INTO report_cell_user_map (report_cell_id, user_id) VALUES " . $valuesToInsert);
								$valuesToInsert = "";
								$valuesToInsertCount = 0;
							} else {
						 		$valuesToInsert .= ",";
							}
						}
						$valuesToInsert .= "(" . $this->id . ", " . $userId . ")";
						$valuesToInsertCount++;
					}

					if ($valuesToInsert) {
						Db_Pdo::execute("INSERT INTO report_cell_user_map (report_cell_id, user_id) VALUES " . $valuesToInsert);
					}
				}

				if (count($usersRemoved)) {
					$reportCellUserListUpdated = true; // this report cell has removed users, force reprocessing of surveys for this report_cell
					$valuesToRemove = "";
					$valuesToRemoveCount = 0;
					foreach($usersRemoved as $userId) {
						if ($valuesToRemove) {
							if ($valuesToRemoveCount > 500) { // Remove records 500 rows at a time
								Db_Pdo::execute("DELETE FROM report_cell_user_map WHERE report_cell_id = " . $this->id . " AND (" . $valuesToRemove . ")");
								$valuesToRemove = "";
								$valuesToRemoveCount = 0;
							} else {
						 		$valuesToRemove .= " OR ";
							}
						}
						$valuesToRemove .= "user_id = " . $userId;
						$valuesToRemoveCount++;
					}

					if ($valuesToRemove) {
						Db_Pdo::execute("DELETE FROM report_cell_user_map WHERE report_cell_id = " . $this->id . " AND (" . $valuesToRemove . ")");
					}
				}

				if ($reportCellUserListUpdated) {
					// The group of users has changed, so force report_cell_survey reprocessing the next time a report is needed
					$sql = "DELETE FROM report_cell_survey WHERE report_cell_id = ?";
					Db_Pdo::execute($sql, $this->id);
				}

				$this->conditions_processed = 1;
				$this->save();
			}
		}
	}

	public function deleteUserMaps() {
		if ($this->id) {
			Db_Pdo::execute("DELETE FROM report_cell_user_map WHERE report_cell_id = ?", $this->id);
		}
	}
}

