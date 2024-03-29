<?

class OldDevadminIndexFunction {
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
		$totalSearches = 0;

		// SELECT user_id, COUNT(user_id) as total_visits FROM metrics_log WHERE user_id > 122 AND metrics_type = 1 AND ((content LIKE '%guitar%' AND content LIKE '%center%') OR (content LIKE '%guitar%' AND content LIKE '%centre%')) GROUP BY user_id ORDER BY user_id

		$request = $this->getRequest();
		$csv = "User ID,Number Of Searches\n";
		$keywords = trim($request->getParam('keywords', ""));
		if ($keywords) {
			$keywordArray = explode("1", $keywords);
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

			$csv .= "Total Users,Total Searches\n";
			$csv .= $totalUsers.",".$totalSearches."\n";
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
				$client = Gaming_BigDoor_HttpClient::getInstance('43bfbce697bd4be99c9bf276f9c6b086', '35eb12f3e87144a0822cf1d18d93d867');
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


	public function raffleMeisterAction () {
		$request = $this->getRequest();
		$goodId = (int) $request->getParam('named_good_id');

		switch ($goodId) {
			case 3810011:
				$startTime = mktime(0, 0, 0, 7, 1, 2012);
				$endTime = mktime(23, 59, 59, 9, 10, 2012);
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
						$client = Gaming_BigDoor_HttpClient::getInstance('db28806caf654e6bbe6b79a103ba50bb', '49a400cfbbed457993798d3c1774f953');
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
		$request = $this->getRequest();
		$surveyId = $request->getParam("survey_id");
		$survey = new Survey();
		$survey->loadData($surveyId);
		$messages = array();
		if ($survey->id) {
			$messages = $survey->retrieveQuestionsAndChoicesFromSurveyGizmo();
		}
		$this->view->messages = $messages;
	}

	public function processSurveyResponsesAction () {
		$request = $this->getRequest();
		$surveyId = $request->getParam("survey_id");
		$survey = new Survey();
		$survey->loadData($surveyId);
		$messages = array();
		if ($survey->id) {
			$messages = $survey->retrieveResponsesFromSurveyGizmo();
		}
		$this->view->messages = $messages;
	}


	public function processAllPollsAction () {
		$surveys = new SurveyCollection();
		$surveys->loadAllSurveys();
		$messages = array();
		foreach ($surveys as $survey) {
			if ($survey->type == "poll" && $survey->processing_status == "pending") {
				$messages = array_merge($messages, $survey->retrieveQuestionsAndChoicesFromSurveyGizmo());
				$messages = array_merge($messages, $survey->retrieveResponsesFromSurveyGizmo());
			}
		}
		$this->view->messages = $messages;
	}


	public function surveyPdfExportAction () {
		$this->_validateRequiredParameters(array('html_to_render'));
		try
		{
			// create an API client instance
			$client = new Pdfcrowd("SaySo", "ce1a34e07ace1bb6d2709068994e3c9f");

			// convert a web page and store the generated PDF into a $pdf variable

			$client->enableJavaScript(false);
			//$client->usePrintMedia(true);
			$client->setPageWidth(1042);

			$pdf = $client->convertHtml($this->html_to_render);

			// set HTTP response headers
			header("Content-Type: application/pdf");
			header("Cache-Control: no-cache");
			header("Accept-Ranges: none");
			header("Content-Disposition: attachment; filename=\"google_com.pdf\"");

			// send the generated PDF
			echo $pdf;
			exit;
		}
		catch(PdfcrowdException $why)
		{
			echo "Pdfcrowd Error: " . $why;
			exit;
		}
	}
}