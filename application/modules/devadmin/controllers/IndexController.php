<?php
/**
 * Actions in this controller are for admin tools/reports meant for internal use (esp. devs)
 */
require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Devadmin_IndexController extends Api_GlobalController
{
	public function preDispatch() {
		// i.e. for everything based on Generic Starbar, use these includes
		$this->view->headLink()->appendStylesheet('/css/starbar-generic.css');
		$this->view->headScript()->appendFile('/js/starbar/jquery-1.7.1.min.js');
		$this->view->headScript()->appendFile('/js/starbar/jquery-ui-1.8.16.custom.min.js');
		$this->view->headScript()->appendFile('/js/starbar/jquery.jscrollpane.min.js');
		$this->view->headScript()->appendFile('/js/starbar/jquery.cookie.js');
		$this->view->headScript()->appendFile('/js/starbar/jquery.jeip.js');
		$this->view->headScript()->appendFile('/js/starbar/jquery.cycle.lite.js');
		$this->view->headScript()->appendFile('/js/starbar/jquery.easyTooltip.js');
	}

	public function indexAction () {

	}

	public function userPageViewTrackingReportAction () {
		$totalPageViews = 0;
		$totalUsers = 0;

		// SELECT user_id, COUNT(user_id) as total_visits FROM metrics_log WHERE user_id > 122 AND metrics_type = 1 AND ((content LIKE '%guitar%' AND content LIKE '%center%') OR (content LIKE '%guitar%' AND content LIKE '%centre%')) GROUP BY user_id ORDER BY user_id

		$request = $this->getRequest();
		$csv = "user_id,number_of_page_views\n";
		$keywords = trim($request->getParam('keywords', ""));
		if ($keywords) {
			$keywordArray = explode(" ", $keywords);
			$keywordClause = "((";
			foreach ($keywordArray as $keyword) {
				if ($keyword == "|") {
					$keywordClause .= ") OR (";
				} else {
					if (substr($keywordClause, -1) != "(") {
						$keywordClause .= " AND ";
					}
					$keywordClause .= "LOWER(content) LIKE '%".strtolower($keyword)."%'";
				}
			}
			$keywordClause .= "))";

			$sql = "SELECT user_id, COUNT(user_id) as page_views FROM metrics_log WHERE user_id > 122 AND metrics_type = 2 AND ".$keywordClause." GROUP BY user_id ORDER BY user_id";
			$reportData = Db_Pdo::fetchAll($sql);
			foreach ($reportData as $row) {
				$csv .= $row['user_id'] . "," . $row['page_views'] . "\n";
				$totalPageViews += (int)$row['page_views'];
				$totalUsers += 1;
			}
		}

		$this->view->total_page_views = $totalPageViews;
		$this->view->total_users = $totalUsers;
		$this->view->keywords = $keywords;
		$this->view->csv = $csv;
	}

	public function userSearchTrackingReportAction () {
		$totalUsers = 0;

		// SELECT user_id, COUNT(user_id) as total_visits FROM metrics_log WHERE user_id > 122 AND metrics_type = 1 AND ((content LIKE '%guitar%' AND content LIKE '%center%') OR (content LIKE '%guitar%' AND content LIKE '%centre%')) GROUP BY user_id ORDER BY user_id

		$request = $this->getRequest();
		$csv = "user_id,number_of_searches\n";
		$keywords = trim($request->getParam('keywords', ""));
		if ($keywords) {
			$keywordArray = explode(" ", $keywords);
			$keywordClause = "((";
			foreach ($keywordArray as $keyword) {
				if ($keyword == "|") {
					$keywordClause .= ") OR (";
				} else {
					if (substr($keywordClause, -1) != "(") {
						$keywordClause .= " AND ";
					}
					$keywordClause .= "LOWER(content) LIKE '%".strtolower($keyword)."%'";
				}
			}
			$keywordClause .= "))";

			$sql = "SELECT user_id, COUNT(user_id) as searches FROM metrics_log WHERE user_id > 122 AND metrics_type = 1 AND ".$keywordClause." GROUP BY user_id ORDER BY user_id";
			$reportData = Db_Pdo::fetchAll($sql);
			foreach ($reportData as $row) {
				$csv .= $row['user_id'] . "," . $row['searches'] . "\n";
				$totalSearches += (int)$row['searches'];
				$totalUsers += 1;
			}
		}

		$this->view->total_searches = $totalSearches;
		$this->view->total_users = $totalUsers;
		$this->view->keywords = $keywords;
		$this->view->csv = $csv;
	}

	public function testUserReportAction () {
		$csv = "user_id,email\n";

		$sql = "
			SELECT user.id, user_email.email
			FROM user INNER JOIN user_email ON user.primary_email_id = user_email.id
			WHERE (user_email.email LIKE '%@say.so'
					OR user_email.email LIKE '%@saysollc.com'
					OR user_email.email LIKE '%@hellomusic.com'
					OR user_email.email LIKE '%@wilshiremedia.com'
				)
				OR user.id < 123
			ORDER BY user.id ASC
		";

		$users = Db_Pdo::fetchAll($sql);

		foreach($users as $user) {
			$csv .= $user['id'] . "," . $user['email'] . "\n";
		}

		$this->view->csv = $csv;
	}

	public function notesReportAction () {
		$notesReportCSV = "user_id,email,first_name,last_name,chops_earned,notes_earned,notes_spent,notes_remaining\n";

		$sql = "
			SELECT user_gaming.user_id, user_gaming.gaming_id, user.first_name, user.last_name, user_email.email
			FROM user_gaming, user, user_email
			WHERE user_gaming.user_id = user.id
				AND user.primary_email_id = user_email.id
				AND user_email.email NOT LIKE '%@say.so'
				AND user_email.email NOT LIKE '%@saysollc.com'
				AND user_email.email NOT LIKE '%@hellomusic.com'
				AND user_email.email NOT LIKE '%@wilshiremedia.com'
				AND user.id > 122
		";

		$gamers = Db_Pdo::fetchAll($sql);

		foreach($gamers as $gamer) {
			$gamerInfoCache = Api_Cache::getInstance('ReportCache_Gamer_' . $gamer['gaming_id'], Api_Cache::LIFETIME_MONTH);
			$gamerInfo = false;
			if ($gamerInfoCache->test()) {
				$gamerInfo = $gamerInfoCache->load();
			}

			if (!$gamerInfo || !$gamerInfo->currency_balances) {
				$client = Gaming_BigDoor_HttpClient::getInstance('2107954aa40c46f090b9a562768b1e18', '76adcb0c853f486297933c34816f1cd2');
				$client->getEndUser($gamer['gaming_id']);
				$gamerInfo = $client->getData();
				$gamerInfoCache->save($gamerInfo);
			}

			$notes = 0;
			$chops = 0;

			foreach ($gamerInfo->currency_balances as $currency) {
				if ((strtolower($currency->end_user_title) == 'notes' || strtolower($currency->pub_title) == 'notes') && intval($currency->current_balance)) {
					$notes = intval($currency->current_balance);
				}
				if ((strtolower($currency->end_user_title) == 'chops' || strtolower($currency->pub_title) == 'chops') && intval($currency->current_balance)) {
					$chops = intval($currency->current_balance);
				}
			}

			$notesReportCSV .= $gamer['user_id'] . ',' . $gamer['email'] . ',' . $gamer['first_name'] . ',' . $gamer['last_name'] . ',' . $chops . ',' . ($chops/10) . ',' . (($chops/10) - $notes) . ',' . $notes . "\n";
		}

		$this->view->notesReportCSV = $notesReportCSV;
	}

	public function inventoryAction () {
		$request = $this->getRequest();
		$goodId = $request->getParam('named_good_id');
		$newInventory = $request->getParam('new_inventory');

		$client = new Gaming_BigDoor_HttpClient('2107954aa40c46f090b9a562768b1e18', '76adcb0c853f486297933c34816f1cd2');
		$client->getNamedGoodCollection(788);
		$data = $client->getData();
		$goods = $data->named_goods;

		$remainingInventory = "";
		$soldInventory = "";

		if ($goodId) {
			$client->namedGoodCollection(788)->namedGood($goodId)->getInventory();
			$data = $client->getData();
			if ($data) {
				$soldInventory = $data->sold_inventory;
				$remainingInventory = $data->total_inventory - $soldInventory;
				if ($newInventory != "") {
					$newInventory = abs($newInventory);
					$remainingInventory = $newInventory;
					$client->setParameterPost('total_inventory', $remainingInventory+$soldInventory);
					$client->namedGoodCollection(788)->namedGood($goodId)->putInventory();

					$game = Game_Starbar::getInstance();
					$cache = Api_Cache::getInstance('BigDoor_getNamedTransactionGroup_store_' . $game->getEconomy()->getKey(), Api_Cache::LIFETIME_WEEK);
					$cache->remove();
				}
			} else {
				if ($newInventory != "") {
					$newInventory = abs($newInventory);
					$remainingInventory = $newInventory;
					$client->setParameterPost('total_inventory', $remainingInventory);
					$client->namedGoodCollection(788)->namedGood($goodId)->postInventory(); // post CREATES inventory

					$game = Game_Starbar::getInstance();
					$cache = Api_Cache::getInstance('BigDoor_getNamedTransactionGroup_store_' . $game->getEconomy()->getKey(), Api_Cache::LIFETIME_WEEK);
					$cache->remove();
				}
			}
		}

		$this->view->named_goods = $goods;
		$this->view->named_good_id = $goodId;
		$this->view->remaining_inventory = $remainingInventory;
		$this->view->sold_inventory = $soldInventory;
	}

	public function raffleMeisterAction () {
		$request = $this->getRequest();
		$goodId = (int) $request->getParam('named_good_id');

		switch ($goodId) {
			case 2036:
				$startTime = mktime(0, 0, 0, 12, 2, 2011);
				$endTime = mktime(23, 59, 59, 12, 11, 2011);
				break;
			case 2038:
				$startTime = mktime(0, 0, 0, 12, 2, 2011);
				$endTime = mktime(23, 59, 59, 18, 11, 2011);
				break;
			case 2044:
				$startTime = mktime(0, 0, 0, 12, 2, 2011);
				$endTime = mktime(23, 59, 59, 12, 25, 2011);
				break;
			case 2054:
				$startTime = mktime(0, 0, 0, 12, 2, 2011);
				$endTime = mktime(23, 59, 59, 1, 2, 2012);
				break;
			default:
				$goodId = false;
		}

		if ($goodId) {
			// BD server is 8 hours ahead
			$startTime = $startTime + (8*60*60);
			$endTime = $endTime + (8*60*60);

			$iterations = 60;
			$step = (int) round(($endTime-$startTime)/$iterations);
			$transactions = array();
			for ( $i=0 ; $i<$iterations ; $i++ ) {
				$stepStartTime = $startTime+($step*$i);
				$stepEndTime = $startTime+($step*($i+1));
				if ($i == $iterations - 1) $stepEndTime = $endTime;

				if ($stepStartTime < mktime() + (9*60*60)) { // Don't check more than one hour into the future (one hour in case of discrepencies between BD and SS timing)
					$cacheId = 'Token_Cache_'.$goodId.'_'.$stepStartTime.'_'.$stepEndTime;
					$cache = Api_Cache::getInstance($cacheId, Api_Cache::LIFETIME_MONTH);

					if ($cache->test()) {
						$transactions = array_merge($transactions, $cache->load());
					} else {
						$client = Gaming_BigDoor_HttpClient::getInstance('2107954aa40c46f090b9a562768b1e18', '76adcb0c853f486297933c34816f1cd2');
						$client->setParameterGet('max_records', 10000);
						$client->setParameterGet('named_good', $goodId);
						$client->setParameterGet('start_time', $stepStartTime);
						$client->setParameterGet('end_time', $stepEndTime);
						$client->getGoodSummary();
						$data = $client->getData();
						if ($stepEndTime < mktime() + (7*60*60)) { // Cache everything that has been purchased more than an hour ago (allow an hour for BD to be up to date)
							$cache->save($data);
						}
						$transactions = array_merge($transactions, $data);
					}
				}
			}
			$this->view->transactions = $transactions;

			$uniqueGamers = new ItemCollection();
			if (count($transactions)) {
				foreach ($transactions as $transaction) {
					$gamerId = $transaction->good_sender;
					if (!$uniqueGamers->hasItem($gamerId)) {
						$uniqueGamer = new Item();
						$uniqueGamer->setId($gamerId);
						$uniqueGamers->addItem($uniqueGamer);
					}
				}
				$uniqueGamersString = "";
				foreach ($uniqueGamers as $uniqueGamer) {
					if ($uniqueGamersString) $uniqueGamersString .= ",";
					$uniqueGamersString .= "'".$uniqueGamer->getId()."'";
				}
				$sql = "
					SELECT user.id AS user_id, user_email.email AS email, user_gaming.gaming_id AS id
					FROM user, user_email, user_gaming
					WHERE user.primary_email_id = user_email.id
						AND user_gaming.user_id = user.id
						AND user_gaming.gaming_id IN (".$uniqueGamersString.")
					ORDER BY FIELD (user_gaming.gaming_id, ".$uniqueGamersString.")
				";
				$results = Db_Pdo::fetchAll($sql);
				$matchedGamers = new ItemCollection();
				foreach ($results as $result) {
					$matchedGamer = new Item();
					$matchedGamer->setId($result['id']);
					$matchedGamer->user_id = $result['user_id'];
					$matchedGamer->email = $result['email'];
					$matchedGamers->addItem($matchedGamer);
				}
				$this->view->matched_gamers = $matchedGamers;
			}
		}

		$this->view->named_good_id = $goodId;
	}

	public function emailsInstalledAction () {
		$sql = "
			SELECT uuid FROM external_user
			WHERE user_id IS NOT NULL
		";
		$results = Db_Pdo::fetchAll($sql);
		$emails = "";
		foreach ($results as $result) {
			if (strpos($result['uuid'], "say.so") === false && strpos($result['uuid'], "saysollc.com") === false && strpos($result['uuid'], "hellomusic.com") === false && strpos($result['uuid'], "@") !== false) {
				if ($emails) $emails .= ",";
				$emails .= $result['uuid'];
			}
		}
		$this->view->emails = $emails;
	}

	public function emailsNotInstalledAction () {
		$sql = "
			SELECT uuid FROM external_user
			WHERE user_id IS NULL
		";
		$results = Db_Pdo::fetchAll($sql);
		$emails = "";
		foreach ($results as $result) {
			if (strpos($result['uuid'], "say.so") === false && strpos($result['uuid'], "saysollc.com") === false && strpos($result['uuid'], "hellomusic.com") === false && strpos($result['uuid'], "@") !== false) {
				if ($emails) $emails .= ",";
				$emails .= $result['uuid'];
			}
		}
		$this->view->emails = $emails;
	}

	public function measureAdsAction () {
		$request = $this->getRequest();

		$operation = $request->getParam("operation", false);
		$studyType = $request->getParam("study_type", false);
		$tagType = $request->getParam("tag_type", false);
		$tag = $request->getParam("tag", false);
		$domain = $request->getParam("domain", false);
		$adTarget = $request->getParam("ad_target", false);
		$studyIdToDelete = $request->getParam("study_id_to_delete", false);

		if ($operation == "delete" && $studyIdToDelete) {
			$study = new Study;
			$study->loadData($studyIdToDelete);
			$study->delete();

			Api_Cache::getInstance('Studies_GetAll_RecentOrder')->remove(); // clear studies cache
		}

		if (
			$operation == "add"
			&& $studyType
			&& $tag
			&& $tagType
			&& ($domain || $tagType == "Facebook")
			&& ($adTarget || $studyType == "creative")
		) {
			$newStudy = new Study();
			$newStudy->study_type = ($studyType == "campaign" ? 2 : 3); // Why is this an integer? :(
			$newStudy->status = 0; // Why is this an integer? :(
			$newStudy->user_id = 1;
			$newStudy->name = $tagType . " AD: " . $tag;
			$newStudy->study_id = $tagType . " AD: " . $tag;
			$newStudy->size = 100;
			$newStudy->size_minimum = 1;
			$newStudy->begin_date = new Zend_Db_Expr('now()');
			$newStudy->end_date = new Zend_Db_Expr('date_add(now(), interval 3 month)');
			$newStudy->is_stopped = 0;
			$newStudy->click_track = 1;
			$newStudy->save();

			$newStudyCell = new Study_Cell();
			$newStudyCell->study_id = $newStudy->id;
			$newStudyCell->description = $tagType . " AD: " . $tag;
			$newStudyCell->size = 100;
			$newStudyCell->cell_type = "test";
			$newStudyCell->save();

			$newStudyTag = new Study_Tag();
			$newStudyTag->user_id = 1;
			$newStudyTag->study_id = $newStudy->id;
			$newStudyTag->name = $tagType . " AD: " . $tag;
			$newStudyTag->type = $tagType;
			$newStudyTag->tag = $tag;
			if ($studyType == "campaign") $newStudyTag->target_url = $adTarget;
			$newStudyTag->save();

			$newStudyCellTagMap = new Study_CellTagMap();
			$newStudyCellTagMap->cell_id = $newStudyCell->id;
			$newStudyCellTagMap->tag_id = $newStudyTag->id;
			$newStudyCellTagMap->save();

			$domain = ($tagType == "Facebook" ? "www.facebook.com" : $domain);

			$newStudyDomain = new Study_Domain();
			$newStudyDomain->loadDataByUniqueFields(array("domain" => $domain));

			if (! $newStudyDomain->id) {
				$newStudyDomain->user_id = 1;
				$newStudyDomain->domain = $domain;
				$newStudyDomain->save();
			}

			$newStudyTagDomainMap = new Study_TagDomainMap();
			$newStudyTagDomainMap->tag_id = $newStudyTag->id;
			$newStudyTagDomainMap->domain_id = $newStudyDomain->id;
			$newStudyTagDomainMap->save();

			if ($studyType == "creative") {
				$newStudyCreative = new Study_Creative();
				$newStudyCreative->user_id = 1;
				$newStudyCreative->name = $tagType . " AD: " . $tag;
				if ($tagType == "Facebook") {
					$newStudyCreative->type = "Facebook";
					$newStudyCreative->url = "https://s3.amazonaws.com/say.so/media/Say.So_FB.jpg";
					$newStudyCreative->ad_title = "Say.So can test your AD here!";
					$newStudyCreative->ad_description = "Pre-test your ad creative with Say.So's revolutionary product ADjuster&trade;";
				} else {
					$newStudyCreative->type = "Image";
					$newStudyCreative->url = "http://s3.amazonaws.com/say.so/ADj+CREATIVE+300x250+PLACEHOLDER.jpg";
					$newStudyCreative->ad_title = "Say.So can test your AD here!";
				}
				$newStudyCreative->target_url = "http://say.so/?".$newStudy->id;
				$newStudyCreative->save();

				$newStudyCreativeTagMap = new Study_CreativeTagMap();
				$newStudyCreativeTagMap->tag_id = $newStudyTag->id;
				$newStudyCreativeTagMap->creative_id = $newStudyCreative->id;
				$newStudyCreativeTagMap->save();
			}

			Api_Cache::getInstance('Studies_GetAll_RecentOrder')->remove(); // clear studies cache
		}

		$currentStudies = new StudyCollection();
		$currentStudies->loadAllTestStudies();

		if( $currentStudies->count() ) {
			$commaDelimitedStudyIdList = "";
			foreach($currentStudies AS $study) {
				if ($commaDelimitedStudyIdList) $commaDelimitedStudyIdList = $commaDelimitedStudyIdList . ",";
				$commaDelimitedStudyIdList = $commaDelimitedStudyIdList . $study->id;
			}

			$sql = "
				SELECT sd.domain
				FROM study_tag st
					LEFT JOIN study_tag_domain_map stdm ON st.id = stdm.tag_id
					LEFT JOIN study_domain sd ON sd.id = stdm.domain_id
				WHERE st.study_id IN (" . $commaDelimitedStudyIdList . ")
				ORDER BY FIND_IN_SET(st.study_id, '" . $commaDelimitedStudyIdList . "')
			";
			$currentDomains = Db_Pdo::fetchAll($sql);
			$this->view->current_domains = $currentDomains;
		}

		$this->view->current_studies = $currentStudies;
	}


	public function processSurveyAction () {
		$config = Api_Registry::getConfig();
		$request = $this->getRequest();
		$surveyId = $request->getParam("survey_id");

		$survey = new Survey();
		$survey->loadData($surveyId);

		$decodedJson = false;
		$questionArray = array();
		$questionExternalIdReferenceArray = array();

		// Messages to show on interface after processing
		$messages = array();

		$surveyQuestionsSaved = 0;
		$surveyQuestionChoicesSaved = 0;

		if ($survey->id && $survey->external_id && $survey->processing_status == "pending") {
			$sgUser = $config->surveyGizmo->api->username;
			$sgPass = $config->surveyGizmo->api->password;

			$requestParams["user:pass"] = $sgUser . ":" . $sgPass;

			$requestParamString = "";
			foreach ($requestParams as $key => $value) {
				if ($requestParamString) $requestParamString .= "&";
				else $requestParamString = "?";

				$requestParamString .= $key . "=" . $value;
			}

			$url = "https://restapi.surveygizmo.com/v1/survey/" . $survey->external_id . "/surveyquestion" . $requestParamString;
			$messages[] = "Connecting to " . $url;

			$handle = fopen($url, 'r');
			set_time_limit(180); // Allow SG 3 minutes to respond

			$json = stream_get_contents($handle);
			if ($json) {
				$decodedJson = json_decode($json, true);
			} else {
				$decodedJson = null;
				throw new Api_Exception(Api_Error::create(Api_Error::SURVEYGIZMO_ERROR, 'Attempt to retreive survey responses failed when accessing: ' . $url));
			}
		}

		if (
			$decodedJson
			&& isset($decodedJson['result_ok'])
			&& isset($decodedJson['total_count'])
			&& isset($decodedJson['total_pages'])
			&& isset($decodedJson['data'])
			&& $decodedJson['result_ok'] === true
			&& $decodedJson['total_count']
			&& $decodedJson['total_pages']
			&& count($decodedJson['data'])
		) {
			$messages[] = "Survey Gizmo reports " . $decodedJson['total_count'] . " questions on " . $decodedJson['total_pages'] . " page(s)";
			$messages[] = "(including action and logic questions, which we don't save, and piped questions which we save as multiple questions)";

			$questionOrdinal = 1;
			$questionsData = $decodedJson['data'];

			for ($indexInQuestionArray = 0; $indexInQuestionArray < count($questionsData); $indexInQuestionArray++) {
				if (isset($questionsData[$indexInQuestionArray]['id']) && ((int) $questionsData[$indexInQuestionArray]['id'])) {
					$questionExternalIdReferenceArray[(int) $questionsData[$indexInQuestionArray]['id']] = $indexInQuestionArray;
				}
			}


			for ($mainQuestionCounter = 0; $mainQuestionCounter < count($questionsData); $mainQuestionCounter++) {
				// Reset time limit to allow for processing, allow for 60 seconds per question
				set_time_limit(60);

				$questionData = $questionsData[$mainQuestionCounter];

				$surveyType = strtolower($questionData['_subtype']);
				$needToSaveQuestionAgain = false;

				// Piped question, single choice from many
				if ($surveyType == "table" && isset($questionData['properties']['piped_from']) && $questionData['properties']['piped_from']) {

					$pipedFrom = (int) $questionData['properties']['piped_from'];
					// The question that was piped from should have already been processed, and therefore should exist in the
					// $questionArray that we create as we process the questions from SG
					if ($pipedFrom && isset($questionArray[$pipedFrom]) && isset($questionArray[$pipedFrom]->option_array)) {
						$pipedFromQuestion = $questionArray[$pipedFrom];

						$pipedQuestion = new Survey_Question();
						$pipedQuestion->survey_id = $survey->id;
						$pipedQuestion->choice_type = 'none';
						$pipedQuestion->data_type = 'none';
						$pipedQuestion->option_array = array();
						$pipedQuestion->piped_from_survey_question_id = $pipedFromQuestion->id; // id in local DB
						$pipedQuestion->title = $questionData['title']['English'];
						$pipedQuestion->external_question_id = (int) $questionData['id'];
						$pipedQuestion->number_of_choices = count($questionData['options']);
						$pipedQuestion->ordinal = $questionOrdinal * 10;
						$questionOrdinal++;

						$pipedQuestion->save();
						$surveyQuestionsSaved++;

						$choiceOrdinal = 1;

						foreach ($questionData['options'] as $optionData) {
							if ($optionData['_type'] == "SurveyOption") {
								$questionChoice = new Survey_QuestionChoice();
								$questionChoice->survey_question_id = $pipedQuestion->id;
								$questionChoice->external_choice_id = (int) $optionData['id'];
								$questionChoice->title = $optionData['title']['English'];
								$questionChoice->value = $optionData['value'];
								$questionChoice->ordinal = $choiceOrdinal * 10;
								$choiceOrdinal++;

								$questionChoice->save();
								$surveyQuestionChoicesSaved++;

								$pipedQuestion->option_array[$questionChoice->external_choice_id] = $questionChoice;
							}
						}

						foreach($pipedFromQuestion->option_array as $pipedOption) { // Each option in the original question is a new question
							$question = new Survey_Question();
							$question->survey_id = $survey->id;
							$question->choice_type = 'single';
							$question->data_type = 'none';
							$question->option_array = array();
							$question->piped_from_survey_question_id = $pipedQuestion->id; // id in local DB
							$question->piped_from_survey_question_choice_id = $pipedOption->id; // id in local DB
							$question->title = $pipedQuestion->title . " : " . $pipedOption->title;
							$question->external_question_id = $pipedQuestion->external_question_id;
							$question->external_pipe_choice_id = $pipedOption->external_choice_id;
							$question->number_of_choices = count($pipedQuestion->option_array);
							$question->ordinal = $questionOrdinal * 10;
							$questionOrdinal++;

							$question->save();
							$surveyQuestionsSaved++;

							// We could duplicate the options for each piped question
							// (that's what commented code block below does), but instead
							// we'll re-use the options from the original question since
							// they should be the same for all piped questions

							/*$choiceOrdinal = 1;
							foreach ($questionData['options'] as $optionData) {
								if ($optionData['_type'] == "SurveyOption") {
									$questionChoice = new Survey_QuestionChoice();
									$questionChoice->survey_question_id = $question->id;
									$questionChoice->external_choice_id = (int) $optionData['id'];
									$questionChoice->title = $optionData['title']['English'];
									$questionChoice->value = $optionData['value'];
									$questionChoice->ordinal = $choiceOrdinal * 10;
									$choiceOrdinal++;

									$questionChoice->save();
									$surveyQuestionChoicesSaved++;

									$question->option_array[$questionChoice->external_choice_id] = $questionChoice;
								}
							}
							*/

							$questionArray[$question->external_pipe_choice_id] = $question; // Add to array so we can easily find later for piping
						}

						$questionArray[$pipedQuestion->external_question_id] = $pipedQuestion; // Add to array so we can easily find later for piping
					}

				// Piped question, text value (string by default)
				} elseif ($surveyType == "textbox" && isset($questionData['properties']['piped_from']) && $questionData['properties']['piped_from']) {

					$pipedFrom = (int) $questionData['properties']['piped_from'];
					if ($pipedFrom && isset($questionArray[$pipedFrom]) && isset($questionArray[$pipedFrom]->option_array)) {
						$pipedFromQuestion = $questionArray[$pipedFrom];

						foreach($pipedFromQuestion->option_array as $pipedOption) { // Each option in the original question is a new question
							$question = new Survey_Question();
							$question->survey_id = $survey->id;
							$question->choice_type = 'none';
							$question->data_type = 'string';
							$question->option_array = array();
							$question->piped_from_survey_question_id = $pipedQuestion->id; // id in local DB
							$question->piped_from_survey_question_choice_id = $pipedOption->id; // id in local DB
							if (isset($questionData['title']['English']) && $questionData['title']['English']) {
								if (strpos($questionData['title']['English'], "[%%PIPED_VALUE%%]")) {
									$question->title = str_replace("[%%PIPED_VALUE%%]", $pipedOption->title, $questionData['title']['English']);
								} else {
									$question->title = $questionData['title']['English'] . " : " . $pipedOption->title;
								}
							} else {
								$question->title = $pipedOption->title;
							}
							$question->external_question_id = (int) $questionData['id'];
							$question->external_pipe_choice_id = $pipedOption->external_choice_id;
							$question->ordinal = $questionOrdinal * 10;
							$questionOrdinal++;

							$question->save();
							$surveyQuestionsSaved++;

							$questionArray[$question->external_pipe_choice_id] = $question; // Add to array so we can easily find later for piping
						}
					}

				// Piped question, single choice from multiple
				} elseif ($surveyType == "radio" && isset($questionData['properties']['piped_from']) && $questionData['properties']['piped_from']) {

					$pipedFrom = (int) $questionData['properties']['piped_from'];
					if ($pipedFrom && isset($questionArray[$pipedFrom]) && isset($questionArray[$pipedFrom]->option_array)) {
						$pipedFromQuestion = $questionArray[$pipedFrom];

						$question = new Survey_Question();
						$question->survey_id = $survey->id;
						$question->choice_type = 'single';
						$question->data_type = 'none';
						$question->piped_from_survey_question_id = $pipedFromQuestion->id; // id in local DB
						$question->title = $questionData['title']['English'];
						$question->external_question_id = (int) $questionData['id'];
						$question->ordinal = $questionOrdinal * 10;
						$questionOrdinal++;

						$question->save();
						$surveyQuestionsSaved++;

						// Options are in the piped-from question, so no need to re-add them

						$questionArray[$question->external_question_id] = $question; // Add to array so we can easily find later for piping
					}

				// Piped question, multiple choice from multiple
				} elseif ($surveyType == "checkbox" && isset($questionData['properties']['piped_from']) && $questionData['properties']['piped_from']) {

					$pipedFrom = (int) $questionData['properties']['piped_from'];
					if ($pipedFrom && isset($questionArray[$pipedFrom]) && isset($questionArray[$pipedFrom]->option_array)) {
						$pipedFromQuestion = $questionArray[$pipedFrom];

						$question = new Survey_Question();
						$question->survey_id = $survey->id;
						$question->choice_type = 'multiple';
						$question->data_type = 'none';
						$question->piped_from_survey_question_id = $pipedFromQuestion->id; // id in local DB
						$question->title = $questionData['title']['English'];
						$question->external_question_id = (int) $questionData['id'];
						$question->ordinal = $questionOrdinal * 10;
						$questionOrdinal++;

						$question->save();
						$surveyQuestionsSaved++;

						// Options are in the piped-from question, so no need to re-add them

						$questionArray[$question->external_question_id] = $question; // Add to array so we can easily find later for piping
					}

				// Parent question that pipes into several sub-questions (by default, they don't have the piped_from property set, so we'll fake it)
				} elseif ($surveyType == "table" && isset($questionData['sub_question_skus']) && $questionData['sub_question_skus'] && count($questionData['sub_question_skus'])) { // Not a piped question, but still a table -- look for sub_question_skus

					$masterQuestionExternalId = (int) $questionData['id'];
					$masterQuestionTitle = (isset($questionData['title']['English']) ? $questionData['title']['English'] : "");

					$question = new Survey_Question();
					$question->survey_id = $survey->id;
					$question->choice_type = 'none';
					$question->data_type = 'none';
					$question->option_array = array();
					$question->piped_from_survey_question_id = $pipedFromQuestion->id; // id in local DB
					$question->title = $questionData['title']['English'];
					$question->external_question_id = (int) $questionData['id'];
					$question->number_of_choices = count($questionData['options']);
					$question->ordinal = $questionOrdinal * 10;
					$questionOrdinal++;

					$question->save();
					$surveyQuestionsSaved++;

					$choiceOrdinal = 1;

					foreach ($questionData['options'] as $optionData) {
						if ($optionData['_type'] == "SurveyOption") {
							$questionChoice = new Survey_QuestionChoice();
							$questionChoice->survey_question_id = $question->id;
							$questionChoice->external_choice_id = (int) $optionData['id'];
							$questionChoice->title = $optionData['title']['English'];
							$questionChoice->value = $optionData['value'];
							$questionChoice->ordinal = $choiceOrdinal * 10;
							$choiceOrdinal++;

							$questionChoice->save();
							$surveyQuestionChoicesSaved++;

							$question->option_array[$questionChoice->external_choice_id] = $questionChoice;
						}
					}

					$questionArray[$question->external_question_id] = $question; // Add to array so we can easily find later for piping

					foreach($questionData['sub_question_skus'] as $subQuestionId) { // for each sub_question
						$indexInQuestionArray = $questionExternalIdReferenceArray[(int) $subQuestionId];

						if (isset($questionsData[$indexInQuestionArray]['id']) && ((int) $questionsData[$indexInQuestionArray]['id']) == ((int) $subQuestionId)) {
							if (!isset($questionsData[$indexInQuestionArray]['title'])) $questionsData[$indexInQuestionArray]['title'] = array('English' => "");
							if (!isset($questionsData[$indexInQuestionArray]['title']['English'])) $questionsData[$indexInQuestionArray]['title']['English'] = "";
							$questionsData[$indexInQuestionArray]['title']['English'] = $masterQuestionTitle . ": " . $questionsData[$indexInQuestionArray]['title']['English'];

							if (!isset($questionsData[$indexInQuestionArray]['properties'])) $questionsData[$indexInQuestionArray]['properties'] = array();
							if (!isset($questionsData[$indexInQuestionArray]['properties']['piped_from'])) $questionsData[$indexInQuestionArray]['properties']['piped_from'] = "0";
							$questionsData[$indexInQuestionArray]['properties']['piped_from'] = $masterQuestionExternalId . "";
						}
					}

				// Non-piped, non-table questions (i.e. all other questions, except 'logic' questions and 'action' questions, which aren't really questions)
				} elseif (in_array($surveyType, array("checkbox", "menu", "radio", "textbox", "rank"))) {

					$question = new Survey_Question();
					$question->survey_id = $survey->id;
					$question->external_question_id = (int) $questionData['id'];
					$question->title = $questionData['title']['English'];
					$question->ordinal = $questionOrdinal * 10;
					$questionOrdinal++;

					switch (strtolower($questionData['_subtype'])) {
						case "checkbox":
							$question->choice_type = 'multiple';
							$question->data_type = 'none';
							$question->option_array = array();
							break;
						case "menu":
						case "radio":
							$question->choice_type = 'single';
							$question->data_type = 'none';
							$question->option_array = array();
							break;
						case "textbox":
							$question->choice_type = 'none';
							$question->data_type = 'string'; // Default to string
							break;
						case "rank":
							$question->choice_type = 'multiple';
							$question->data_type = 'integer';
							$question->option_array = array();
							break;
						default:
							break;
					}

					$question->number_of_choices = (isset($questionData['options']) ? count($questionData['options']) : 0);
					$question->save(); // save so we have the id available
					$surveyQuestionsSaved++;

					if ($question->number_of_choices) {
						$choiceOrdinal = 1;
						foreach ($questionData['options'] as $optionData) {
							if ($optionData['_type'] == "SurveyOption") {
								$questionChoice = new Survey_QuestionChoice();
								$questionChoice->survey_question_id = $question->id;
								$questionChoice->external_choice_id = (int) $optionData['id'];
								$questionChoice->title = $optionData['title']['English'];
								$questionChoice->value = $optionData['value'];
								$questionChoice->ordinal = $choiceOrdinal * 10;
								$choiceOrdinal++;

								if (isset($optionData['properties']['other']) && $optionData['properties']['other'] && $question->data_type == 'none') {
									$questionChoice->other = true;
									$question->data_type = 'string'; // question is saved again below
									$needToSaveQuestionAgain = true;
								}

								$questionChoice->save();
								$surveyQuestionChoicesSaved++;

								$question->option_array[$questionChoice->external_choice_id] = $questionChoice;
							}
						}
					}

					if ($needToSaveQuestionAgain) $question->save();

					$questionArray[$question->external_question_id] = $question; // Add to array so we can easily find later for piping
				}

			} // End of main loop through questions

			if ($questionOrdinal - 1) {
				$survey->number_of_questions = $questionOrdinal - 1;
				if (!$survey->display_number_of_questions) $survey->display_number_of_questions = $survey->number_of_questions . "";
			}

			$survey->processing_status = "completed";
			$survey->save();

			$messages[] = "";
			$messages[] = "Processing Complete!";
			$messages[] = "survey_question records saved in the DB: " . $surveyQuestionsSaved;
			$messages[] = "survey_question_response records saved in DB: " . $surveyQuestionChoicesSaved;

			$this->view->messages = $messages;
		}

	}


	public function processSurveyResponsesAction () {
		$config = Api_Registry::getConfig();
		$request = $this->getRequest();
		$surveyId = $request->getParam("survey_id");

		$survey = new Survey();
		$survey->loadData($surveyId);

		$decodedJson = false;
		$requestParams = array();

		// Reference arrays
		$questionIdReferenceArray = array();
		$comboExternalIdReferenceArray = array();
		$choiceExternalIdReferenceArray = array();
		$choiceValueReferenceArray = array();

		// Messages to show on interface after processing
		$messages = array();

		$rowsMatchingRegex = 0;
		$rowsMatchingNoRegex = 0;
		$surveyQuestionResponsesSaved = 0;

		$currentPage = 1;
		$totalNumberOfPages = 1;

		if ($survey->id && $survey->external_id) {
			$sgUser = $config->surveyGizmo->api->username;
			$sgPass = $config->surveyGizmo->api->password;

			$requestParams["user:pass"] = $sgUser . ":" . $sgPass;
			$requestParams["resultsperpage"] = 25;

			// For more on SG filters: http://developer.surveygizmo.com/resources/filtering-and-browsing-results/
			// $requestParams["filter[field][0]"] = "status";
			// $requestParams["filter[operator][0]"] = "="; // Can also use "!=" here
			// $requestParams["filter[value][0]"] = "Complete"; // Can also use "Deleted" here

			while ($currentPage <= $totalNumberOfPages) {
				$requestParams["page"] = $currentPage;

				$requestParamString = "";
				foreach ($requestParams as $key => $value) {
					if ($requestParamString) $requestParamString .= "&";
					else $requestParamString = "?";

					$requestParamString .= $key . "=" . $value;
				}

				$url = "https://restapi.surveygizmo.com/v1/survey/" . $survey->external_id . "/surveyresponse" . $requestParamString;
				$messages[] = "Connecting to " . $url;

				set_time_limit(180); // Allow 3 minutes for SG response (excludes local processing time, since we reset timer below)
				$handle = fopen($url, 'r');
				$json = stream_get_contents($handle);

				if ($json) {
					$decodedJson = json_decode($json, true);
				} else {
					$decodedJson = null;
					throw new Api_Exception(Api_Error::create(Api_Error::SURVEYGIZMO_ERROR, 'Attempt to retreive survey responses failed when accessing: ' . $url));
				}

				if (
					$decodedJson
					&& isset($decodedJson['result_ok'])
					&& isset($decodedJson['total_count'])
					&& isset($decodedJson['total_pages'])
					&& isset($decodedJson['data'])
					&& $decodedJson['result_ok'] === true
					&& $decodedJson['total_count']
					&& $decodedJson['total_pages']
					&& count($decodedJson['data'])
				) {
					// Initialize reference arrays so we don't have to repeatedly call the DB
					if ($currentPage == 1) {
						$messages[] = "Survey Gizmo reports " . $decodedJson['total_count'] . " responses on " . $decodedJson['total_pages'] . " pages";

						$totalNumberOfPages = (int) $decodedJson['total_pages'];

						// for testing:
						// if ($totalNumberOfPages > 5) $totalNumberOfPages = 5;

						$allSurveyQuestions = new Survey_QuestionCollection();
						$allSurveyQuestions->loadAllQuestionsForSurvey($surveyId);

						$allSurveyQuestionChoices = new Survey_QuestionChoiceCollection();
						$allSurveyQuestionChoices->loadAllQuestionChoicesForSurvey($surveyId);

						$surveyQuestionChoices = new Survey_QuestionChoiceCollection();

						// Prepare reference arrays to make finding questions easy
						foreach ($allSurveyQuestions as $surveyQuestion) {
							$questionIdReferenceArray[$surveyQuestion->id] = array("question" => $surveyQuestion, "choices" => array());
							if ($surveyQuestion->choice_type == "multiple") {
								$surveyQuestionChoices->loadAllQuestionChoicesForSurveyQuestion($surveyQuestion->id);
								foreach ($surveyQuestionChoices as $surveyQuestionChoice) {
									$comboArrayKey = $surveyQuestion->external_question_id . "-" . $surveyQuestionChoice->external_choice_id;
									$comboExternalIdReferenceArray[$comboArrayKey] = $surveyQuestion;
								}
							} else {
								$comboArrayKey = $surveyQuestion->external_question_id . "-" . $surveyQuestion->external_pipe_choice_id;
								$comboExternalIdReferenceArray[$comboArrayKey] = $surveyQuestion;
							}
						}

						// Reference arrays for this survey's choices
						foreach ($allSurveyQuestionChoices as $surveyQuestionChoice) {
							$choiceExternalIdReferenceArray[$surveyQuestionChoice->external_choice_id] = $surveyQuestionChoice;
							$choiceValueReferenceArray[$surveyQuestionChoice->value] = $surveyQuestionChoice;
							$questionIdReferenceArray[$surveyQuestionChoice->survey_question_id]["choices"][$surveyQuestionChoice->value] = $surveyQuestionChoice;
						}

						// How the array keys can look for the responses we want to parse
						$regexArray = array(
							"/\[question\(([0-9]+)\), option\(([0-9]+)\)\]/",
							"/\[question\(([0-9]+)\), option\(\"([0-9]+)-(other)\"\)\]/",
							"/\[question\(([0-9]+)\)\]/",
							"/\[question\(([0-9]+)\), question_pipe\(([0-9]+)\)\]/",
							"/\[variable\(([0-9]+)\)\]/",
							"/\[variable\(\"([0-9]+)-shown\"\)\]/",
							"/\[variable\(([0-9]+)\), question_pipe\(([0-9]+)\)\]/",
							"/\[variable\(\"([0-9]+)-shown\"\), question_pipe\(([0-9]+)\)\]/",
						);
					}

					// All the responses on this page of results
					$responsesData = $decodedJson['data'];

					// Go through all the responses on this page (should be one per user)
					foreach ($responsesData as $responseData) {
						// Reset time limit to allow for processing, allow for 60 seconds per response
						set_time_limit(60);
						$externalResponseId = 0;
						$userId = 0;
						$dataToSave = array();

						// Go through this responses's answers (i.e. all the answers one user gave)
						foreach ($responseData as $answerKey => $answerValue) {
							if ($answerKey == "id") {
								$externalResponseId = (int) $answerValue;
							} elseif ($answerKey == "status") {
								if ($answerValue == "Complete" || $answerValue == "Disqualified") {
									continue;
								} else {
									// Skip Partial (or other?) responses
									$externalResponseId = 0;
									$userId = 0;
									$dataToSave = array();
									break;
								}
							// Look for the user_id in the bundle_of_joy, which starts like this: "user_id^-^123^|^..."
							} elseif (strpos($answerValue, "user_id^-^") !== false) {
								$userId = (int) substr($answerValue, 10, strpos($answerValue, "^|^")-10);
							} elseif ($answerValue) { // Skip empty answers
								$matches = array(); // array for preg_match() to write to
								$matchFound = false; // boolean, has the answer key matched any of our regular expressions?
								$matchRegex = ""; // The regex expression that successfully matched this answer
								$matchQuestionExternalId = ""; // The matching question id on SG
								$matchChoiceExternalId = ""; // The matching choice id on SG (can be for multiple choice (with multiple user choices, e.g. checkbox) or for piped questions
								$matchComboArrayKey = ""; // either "$matchQuestionExternalId-" (note dash at the end) or "$matchQuestionExternalId-$matchChoiceExternalId"
								foreach ($regexArray as $regex) {
									$numberOfMatchesFound = preg_match($regex, $answerKey, $matches);
									if ($numberOfMatchesFound) {
										$matchFound = true;
										$matchRegex = $regex;

										$matchQuestionExternalId = $matches[1];
										$matchComboArrayKey = $matchQuestionExternalId . "-";

										if (count($matches) >= 3) {
											$matchChoiceExternalId = $matches[2];
											$matchComboArrayKey .= $matchChoiceExternalId;
										}

										if (count($matches) == 4) $otherValue = true;
										else $otherValue = false;

										break;
									}
								}

								// This answer (within a larger response) matches one of our regular expressions, get the choice/typed in answer out of it
								if ($matchFound) {
									$rowsMatchingRegex++;

									if (isset($comboExternalIdReferenceArray[$matchComboArrayKey])) {
										$matchQuestion = $comboExternalIdReferenceArray[$matchComboArrayKey];
									} else {
										$matchQuestion = null;
										$messages[] = "Question matches regex but not found in \$comboExternalIdReferenceArray: Key = " . $answerKey;
									}

									if ($matchQuestion) {
										$matchChoice = null;
										if ($otherValue || (strpos($matchRegex, "variable") === false && $matchQuestion->data_type != "none")) {
											if (isset($questionIdReferenceArray[$matchQuestion->id]["choices"][$answerValue])) $matchChoice = $questionIdReferenceArray[$matchQuestion->id]["choices"][$answerValue];
											else {
												switch($matchQuestion->data_type) {
													case "string":
														$dataToSave[$matchQuestion->id . "-data"] = array($matchQuestion, $answerValue);
														break;
													case "integer":
														$dataToSave[$matchQuestion->id . "-data"] = array($matchQuestion, intval($answerValue));
														break;
													case "decimal":
													case "monetary":
														$dataToSave[$matchQuestion->id . "-data"] = array($matchQuestion, floatval(str_replace("\$", "", $answerValue)));
														break;
													default:
														$messages[] = "Dunno what to do with this row (question id " . $matchQuestion->id . " matched but unknown data type): " . $answerKey . " => " . $answerValue;
														break;
												}
											}
										} else {
											if (strpos($matchRegex, "variable") !== false && isset($choiceExternalIdReferenceArray[$answerValue])) $matchChoice = $choiceExternalIdReferenceArray[$answerValue];
											elseif (count($matches) >= 3 && isset($choiceExternalIdReferenceArray[$matches[2]])) $matchChoice = $choiceExternalIdReferenceArray[$matches[2]];
											elseif (isset($choiceValueReferenceArray[$answerValue])) $matchChoice = $choiceValueReferenceArray[$answerValue];
											else $messages[] = "Dunno what to do with this row: " . $answerKey . " => " . $answerValue;
										}
										if ($matchChoice) $dataToSave[$matchQuestion->id . "-" . $matchChoice->id] = array($matchQuestion, $matchChoice);
									} else {
										$messages[] = "Unexpected result with this row: " . $answerKey . " => " . $answerValue;
									}
								} else {
									$rowsMatchingNoRegex++;
								}
							}
						} // end processing all answers (and non-answer data) for one user's response

						// $dataToSave has been collected... save it!
						if ($externalResponseId && $userId && count($dataToSave)) {
							$surveyResponse = new Survey_Response();
							$surveyResponse->loadDataByUniqueFields(array("user_id" => $userId, "survey_id" => $survey->id, "processing_status" => "pending"));

							if ($surveyResponse->id) {
								foreach ($dataToSave as $dataKey => $surveyQuestionResponseData) {
									$surveyQuestionResponse = new Survey_QuestionResponse();
									$surveyQuestionResponse->survey_response_id = $surveyResponse->id;
									$surveyQuestionResponse->survey_question_id = $surveyQuestionResponseData[0]->id;
									if (strpos($dataKey, "-data") !== false) {
										$surveyQuestionResponse->data_type = $surveyQuestionResponseData[0]->data_type;
										switch($surveyQuestionResponse->data_type) {
											case "string":
												$surveyQuestionResponse->response_string = $surveyQuestionResponseData[1];
												break;
											case "integer":
												$surveyQuestionResponse->response_integer = $surveyQuestionResponseData[1];
												break;
											case "decimal":
											case "monetary":
												$surveyQuestionResponse->response_decimal = $surveyQuestionResponseData[1];
												break;
											default:
												$messages[] = "Survey Question Response should have data type but doesn't! Data key = " . $dataKey;
												break;
										}
									} else {
										$surveyQuestionResponse->data_type = "choice";
										$surveyQuestionResponse->survey_question_choice_id = $surveyQuestionResponseData[1]->id;
									}
									$surveyQuestionResponse->save();
									$surveyQuestionResponsesSaved++;
								}

								$surveyResponse->data_download = new Zend_Db_Expr('now()');
								$surveyResponse->processing_status = "completed";
								$surveyResponse->save();
							}
						} // data for this response (i.e. all the answers for one user) done being saved!
					} // end of processing for each response (i.e. 1 per user)

					// At this point, we have processed all the responses on this page

					// Fetch next page of results
					$currentPage++;
				}
			} // Done going through all pages of results

			$messages[] = "";
			$messages[] = "Processing Complete!";
			$messages[] = "Rows matching one of the regular expressions: " . $rowsMatchingRegex;
			$messages[] = "survey_question_response records saved in DB: " . $surveyQuestionResponsesSaved;
			$messages[] = "Note that rows saved is usually less than rows matching, since some data is repeated in SG's response";
			$messages[] = "Rows not matching any of the regular expressions (non-zero expected): " . $rowsMatchingNoRegex;

			$this->view->messages = $messages;
		}
	}
}
