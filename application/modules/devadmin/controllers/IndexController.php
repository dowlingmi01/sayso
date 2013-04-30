<?php
/**
 * Actions in this controller are for admin tools/reports meant for internal use (esp. devs)
 */
require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Devadmin_IndexController extends Api_GlobalController
{
	protected $single_starbar_id = null;

	public function preDispatch() {
		// i.e. for everything based on Generic Starbar, use these includes
		$this->view->headLink()->appendStylesheet('/css/starbar-generic.css');
		$this->view->headScript()->appendFile('/js/starbar/jquery-1.7.1.min.js');
		$this->view->headScript()->appendFile('/js/starbar/jquery-ui-1.8.16.custom.min.js');
		$this->view->headScript()->appendFile('/js/starbar/jquery.jscrollpane.min.js');
		$this->view->headScript()->appendFile('/js/starbar/jquery.cookie.js');
		$this->view->headScript()->appendFile('/js/starbar/jquery.jeip.js');
		$this->view->headScript()->appendFile('/js/starbar/jquery.cycle.all.js');
		$this->view->headScript()->appendFile('/js/starbar/jquery.easyTooltip.js');
		$this->view->headTitle()->set("ADj Tester " . (in_array(APPLICATION_ENV, array('development', 'sandbox', 'testing', 'staging')) ? " - " . ucwords(APPLICATION_ENV) : ""));
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

	public function inventoryAction () {
		$request = $this->getRequest();

		$starbarId = $request->getParam('starbar_id');
		$goodId = $request->getParam('named_good_id');
		$newInventory = $request->getParam('new_inventory');

		$sql = "SELECT *
				FROM starbar
				WHERE id > 2
				";
		$starbars = Db_Pdo::fetchAll($sql);
		$this->view->starbars = $starbars;

		if ($starbarId) {
			$starbar = new Starbar();
			$starbar->loadData($starbarId);
			$request->setParam('user_id', 1);

			$this->view->starbar_id = $starbar->id;

			$sql = "SELECT id, name
			          FROM game_purchasable_view
			         WHERE economy_id = ? AND type <> 'token'";
			$goods = Db_Pdo::fetchAll($sql, $starbar->economy_id);

			$remainingInventory = "N/A";
			$soldInventory = "N/A (probably)";

			if ($goodId) {
				$sql = 'SELECT credits, debits FROM game_balance WHERE game_asset_id = ? AND user_id = ?';
				$result = Db_Pdo::fetchAll($sql, $goodId, Game_Transaction::HOUSE_USER_ID);

				if (count($result) && ($data = $result[0])) {
					$soldInventory = $data['debits'];
					$remainingInventory = $data['credits'] - $data['debits'];
				}
				if ($newInventory != "") {
					$newInventory = abs($newInventory);

					Game_Transaction::run( Game_Transaction::HOUSE_USER_ID, $starbar->economy_id, 'ADJUST_STOCK'
						                 , array('asset_id'=>$goodId, 'quantity'=>$newInventory));
					// To avoid reloading the form and setting the inventory again
					$this->_redirect("/devadmin/index/inventory?starbar_id=".$starbarId."&named_good_id=".$goodId);
				}
			}

			$this->view->named_goods = $goods;
			$this->view->named_good_id = $goodId;
			$this->view->remaining_inventory = $remainingInventory;
			$this->view->sold_inventory = $soldInventory;
		}
	}

	public function goodsAction () {
		$request = $this->getRequest();

		$operation = $request->getParam('operation');
		$starbarId = $request->getParam('starbar_id');

		$sql = "SELECT *
				FROM starbar
				WHERE id > 2
				";
		$starbars = Db_Pdo::fetchAll($sql);
		$this->view->starbars = $starbars;

		if ($starbarId)
			$this->view->starbar_id = $starbarId;

		if ($operation == "add") {
			// User-entered data
			$productTitle = $request->getParam('product_title');
			$initialInventory = (int) abs($request->getParam('initial_inventory'));
			$imageUrlFull = $request->getParam('image_url_full');
			$imageUrlPreview = $request->getParam('image_url_preview');
			$imageUrlPreviewBought = $request->getParam('image_url_preview_bought');
			$price = (int) abs($request->getParam('price'));
			$type = $request->getParam('type');

			Game_Transaction::addGood($starbarId, array(
				'description' => $productTitle,
				'bdid' => null,
				'type' => ($type == 'token' ? 'token' : 'physical'),
				'cost' => $price,
				'img_url' => $imageUrlFull,
				'img_url_preview' => $imageUrlPreview,
				'img_url_preview_bought' => $imageUrlPreviewBought
			), $initialInventory);
			echo "Good Added.";
			exit;
		}
	}

	public function raffleMeisterAction () {
		$this->view->headScript()->appendFile('//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js');
		$request = $this->getRequest();
		$operation = $request->getParam('operation');
		$starbarId = $request->getParam('starbar_id');
		$goodId = $request->getParam('good_id');

		$tokens = array();
		$quantities = array();

		$sql = "SELECT *
				FROM starbar
				WHERE id > 1
				";
		$starbars = Db_Pdo::fetchAll($sql);

		$this->view->starbars = $starbars;
		$this->view->operation = $operation;
		$this->view->starbar_id = $starbarId;
		$this->view->good_id = $goodId;

		if ($starbarId) {
			$economyId = Economy::getIdforStarbar($starbarId);

			$this->view->starbar_id = $starbarId;

			$sql = "
				SELECT ugoh.game_asset_id, p.name, SUM(ugoh.quantity) AS total_purchased
				  FROM user_gaming_order_history ugoh, game_purchasable_view p
				 WHERE ugoh.game_asset_id = p.id
				   AND p.economy_id = ?
				   AND p.type = 'token'
				GROUP BY ugoh.game_asset_id
				ORDER BY total_purchased DESC
			";

			$results = Db_Pdo::fetchAll($sql, $starbarId);
			$tokens = array();
			foreach( $results as $token )
				$tokens[$token['game_asset_id']] = $token;
		}

		$this->view->tokens = $tokens;

		$this->view->winning_transaction = new GamerOrderHistory();

		if ($operation == "pick-winner" && $goodId && isset($tokens[$goodId])) {
			$token = $tokens[$goodId];

			$totalQuantityPurchased = $token["total_purchased"];
			$randomWinner = mt_rand(1, $totalQuantityPurchased);

			Db_Pdo::execute("SET @cumulative_sum = 0");

			$sql = "
				SELECT ugoh.*, (@cumulative_sum := @cumulative_sum + ugoh.quantity) as cumulative_quantity
				FROM user_gaming_order_history ugoh
				WHERE game_asset_id = ?
				HAVING cumulative_quantity >= ?
				ORDER BY ugoh.id ASC
				LIMIT 1;
			";
			$winningTransactionResult = Db_Pdo::fetch($sql, $goodId, $randomWinner);

			if ($winningTransactionResult) {
				$this->view->winning_transaction = new GamerOrderHistory($winningTransactionResult);
				$this->view->winning_gamer = new Gamer();
				$this->view->winning_user = new User();
				$this->view->winning_user->loadData($this->view->winning_transaction->user_id);
				$this->view->winning_user_email = new User_Email();
				if ($this->view->winning_user->id && $this->view->winning_user->primary_email_id) {
					$this->view->winning_user_email->loadData($this->view->winning_user->primary_email_id);
				}
			}
		}

	}

	/*
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
	}*/

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
		$studyAdIdToDelete = $request->getParam("study_ad_id_to_delete", false);

		if ($operation == "delete" && $studyAdIdToDelete) {
			$studyAd = new Study_Ad();
			$studyAd->loadData($studyAdIdToDelete);
			$studyAd->delete();

			Api_Cache::getInstance('Study_Ads_GetAll_RecentOrder')->remove(); // clear studies cache
		}

		if (
			$operation == "add"
			&& $studyType
			&& $tag
			&& $tagType
			&& ($domain || $tagType == "facebook")
			&& ($adTarget || $studyType == "creative")
		) {
			$newStudyAd = new Study_Ad();
			$newStudyAd->type = $studyType;
			$newStudyAd->existing_ad_type = $tagType;
			$newStudyAd->existing_ad_tag = $tag;
			$newStudyAd->existing_ad_domain = ($tagType == "facebook" ? "www.facebook.com" : $domain);

			if ($studyType == "creative") {
				if ($tagType == "facebook") {
					$newStudyAd->replacement_ad_type = "facebook";
					$newStudyAd->replacement_ad_url = "https://s3.amazonaws.com/say.so/media/Say.So_FB.jpg";
					$newStudyAd->replacement_ad_title = "Say.So can test your AD here!";
					$newStudyAd->replacement_ad_description = "Pre-test your ad creative with Say.So's revolutionary product, ADjuster&trade;";
				} else {
					$newStudyAd->replacement_ad_type = "image";
					$newStudyAd->replacement_ad_url = "http://s3.amazonaws.com/say.so/ADj+CREATIVE+300x250+PLACEHOLDER.jpg";
					$newStudyAd->replacement_ad_title = "Say.So can test your AD here!";
				}
				$newStudyAd->ad_target = "http://say.so/";
				$newStudyAd->save();
				$newStudyAd->ad_target = "http://say.so/?".$newStudyAd->id;
				$newStudyAd->save();
			} else {
				$newStudyAd->ad_target = $adTarget;
				$newStudyAd->save();
			}

			if ($newStudyAd->id) {
				$reportCellForViews = new ReportCell();
				$reportCellForViews->title = "Viewed study ID " . $newStudyAd->id . " - " . $newStudyAd->existing_ad_type . " AD: " . $newStudyAd->existing_ad_tag;
				$reportCellForViews->condition_type = "and";
				$reportCellForViews->category = "Study";
				$reportCellForViews->save();

				if ($reportCellForViews->id) {
					$userConditionForViews = new ReportCell_UserCondition();
					$userConditionForViews->report_cell_id = $reportCellForViews->id;
					$userConditionForViews->condition_type = "study_ad";
					$userConditionForViews->comparison_type = "viewed";
					$userConditionForViews->compare_study_ad_id = $newStudyAd->id;
					$userConditionForViews->save();
				}

				$reportCellForClicks = new ReportCell();
				$reportCellForClicks->title = "Clicked study ID " . $newStudyAd->id . " - " . $newStudyAd->existing_ad_type . " AD: " . $newStudyAd->existing_ad_tag;
				$reportCellForClicks->condition_type = "and";
				$reportCellForClicks->category = "Study";
				$reportCellForClicks->save();

				if ($reportCellForClicks->id) {
					$userConditionForClicks = new ReportCell_UserCondition();
					$userConditionForClicks->report_cell_id = $reportCellForClicks->id;
					$userConditionForClicks->condition_type = "study_ad";
					$userConditionForClicks->comparison_type = "clicked";
					$userConditionForClicks->compare_study_ad_id = $newStudyAd->id;
					$userConditionForClicks->save();
				}
			}

			Api_Cache::getInstance('Study_Ads_GetAll_RecentOrder')->remove(); // clear studies cache
		}

		$currentStudyAds = new Study_AdCollection();
		$currentStudyAds->loadAllStudyAds();

		$this->view->current_study_ads = $currentStudyAds;
	}


	/*public function measureAdsAction () {
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
	}*/


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


	public function userGroupEditorAction () {
		$this->view->headScript()->appendFile('//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js');
		$this->view->headScript()->appendFile('/js/devadmin/dig.js');
		$this->view->headLink()->appendStylesheet('/css/devadmin/dig.css');

		$this->view->report_cell_id = $this->report_cell_id;

		$sql = "SELECT *
				FROM starbar
				ORDER BY id
				";
		$starbars = Db_Pdo::fetchAll($sql);
		$this->view->starbars = $starbars;

		$surveys = SurveyCollection::getAllSurveysForAllStarbars();
		$this->view->surveys = $surveys;

	}


	public function userGroupEmailsAction () {
		$this->view->headScript()->appendFile('//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js');
		$this->view->headScript()->appendFile('/js/devadmin/dig.js');
		$this->view->headLink()->appendStylesheet('/css/devadmin/dig.css');

		$this->view->report_cell_id = $this->report_cell_id;

		$sql = "SELECT *
				FROM starbar
				ORDER BY id
				";
		$starbars = Db_Pdo::fetchAll($sql);
		$this->view->starbars = $starbars;

		$surveys = SurveyCollection::getAllSurveysForAllStarbars();
		$this->view->surveys = $surveys;

		if (((int)$this->report_cell_id) > 1) {
			$sql = "
				SELECT email
				FROM user_email
				INNER JOIN report_cell_user_map
					ON user_email.user_id = report_cell_user_map.user_id
					AND report_cell_user_map.report_cell_id = ?
				ORDER BY user_email.user_id
			";
			$emails = Db_Pdo::fetchColumn($sql, $this->report_cell_id);
			$this->view->emails = implode(",", $emails);
		}
	}


	public function surveyResponsesAction () {
		$request = $this->getRequest();
		$reportCellId = (int) $request->getParam("report_cell_id", 1);
		$reportCell = new ReportCell();
		$surveyId = (int) $request->getParam("survey_id", false);
		$survey = new Survey();

		$reportCell->loadData($reportCellId);

		if ($surveyId) {
			$survey->loadData($surveyId);
		}

		if ($survey->id) {
			$sql = "
				SELECT sr.user_id, sr.id
				FROM survey_response sr
				INNER JOIN report_cell_user_map rcum
					ON rcum.report_cell_id = ?
					AND (rcum.report_cell_id = ? OR sr.user_id = rcum.user_id)
				INNER JOIN user u
					ON sr.user_id = u.id
					AND u.type != 'test'
				WHERE sr.survey_id = ?
				ORDER BY sr.user_id
			";
			$responses = Db_Pdo::fetchAll($sql, $reportCellId, ReportCell::ALL_USERS_REPORT_CELL, $surveyId);

			if ($responses) {
				$pstTime = new DateTime("now", new DateTimeZone('PDT'));
				$filename = preg_replace("/[,.]+/", "", $pstTime->format("Ymd-Hi") . " ". $survey->title . " - " . $reportCell->title . " - Responses");
				$filename .= ".csv";

				// HTTP Header for CSV file
				header("Content-type: text/csv");
				header("Content-Disposition: attachment; filename=".$filename);
				header("Pragma: no-cache");
				header("Expires: 0");

				// CSV Header Row
				echo "User ID,srid\n";

				foreach ($responses as $response) {
					echo $response['user_id'] . "," . $response['id'] . "\n";
				}

				exit;
			}
		}
	}


	public function surveyReportAction () {
		// increase memory limit for this session only
		ini_set('memory_limit', '512M');

		$this->view->headLink()->appendStylesheet('/css/dig/dig.css');
		$this->view->headScript()->appendFile('//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js');
		$this->view->headScript()->appendFile('/js/dig/dig.js');
		$this->view->headScript()->appendFile('//www.google.com/jsapi');

		$request = $this->getRequest();
		$surveyId = (int) $request->getParam("survey_id", false);
		$reportCellId = (int) $request->getParam("report_cell_id", 1);
		$surveyType = $request->getParam("survey_type");

		if ($this->single_starbar_id) {
			$starbarId = $this->single_starbar_id;
		} else {
			$starbarId = (int) $request->getParam("starbar_id");
		}

		$surveyQuestions = null;
		$calculationArray = array();

		$survey = new Survey();

		if ($surveyId) {
			$survey->loadData($surveyId);
		}

		if ($survey->id && $reportCellId) {
			$reportCellSurvey = new ReportCell_Survey();
			$reportCellSurvey->loadDataByUniqueFields(array("report_cell_id" => $reportCellId, "survey_id" => $surveyId));

			// Survey/report cell combination has never been processed before (or was deleted)
			if (!$reportCellSurvey->id) {
				$reportCellSurvey->report_cell_id = $reportCellId;
				$reportCellSurvey->survey_id = $surveyId;
				$reportCellSurvey->save();
			}

			if ($reportCellSurvey->id) {
				// Survey has been taken since the last time it was processed, so re-process it
				if ($reportCellSurvey->last_processed < $survey->last_response) {
					$reportCellSurvey->process();
				}

				$surveyQuestions = new Survey_QuestionCollection();
				$surveyQuestions->loadAllQuestionsForSurvey($surveyId);

				// Place survey questions into an array, where the key is the survey_question_id
				foreach ($surveyQuestions as $surveyQuestion) {
					$calculationArray[$surveyQuestion->id] = array();
				}

				$surveyQuestionChoices = new Survey_QuestionChoiceCollection();
				$surveyQuestionChoices->loadAllChoicesForSurvey($surveyId);

				foreach ($surveyQuestionChoices as $surveyQuestionChoice) {
					$surveyQuestions[$surveyQuestionChoice->survey_question_id]->option_array[$surveyQuestionChoice->id] = $surveyQuestionChoice;
				}

				$reportCellSurveyCalculations = new ReportCell_SurveyCalculationCollection();
				$reportCellSurveyCalculations->loadAllCalculationsForReportCellSurvey($reportCellSurvey->id);

				foreach ($reportCellSurveyCalculations as $reportCellSurveyCalculation) {
					switch ($reportCellSurveyCalculation->parent_type) {
						case "survey_question":
							$calculationArray[$reportCellSurveyCalculation->survey_question_id][0] = $reportCellSurveyCalculation;
							break;

						case "survey_question_choice":
							$calculationArray[$reportCellSurveyCalculation->survey_question_id][$reportCellSurveyCalculation->survey_question_choice_id] = $reportCellSurveyCalculation;
							break;
					}
				}
			}
		}

		$starbarFilterClause = "";
		if ($this->single_starbar_id) {
			$starbarFilterClause = " WHERE id = " . $this->single_starbar_id . " ";
		}

		$sql = "SELECT *
				FROM starbar
				" . $starbarFilterClause . "
				ORDER BY id
				";
		$starbars = Db_Pdo::fetchAll($sql);
		$this->view->starbars = $starbars;
		$this->view->starbar_id = $starbarId;
		$this->view->single_starbar_id = $this->single_starbar_id;
		$this->view->survey_type = $surveyType;

		$this->view->survey_id = $surveyId;
		$this->view->report_cell_id = $reportCellId;

		if ($reportCellId) {
			$reportCell = new ReportCell();
			$reportCell->loadData($reportCellId);
			$this->view->chosen_report_cell = $reportCell;
		}

		$this->view->calculation_array = $calculationArray;
		$this->view->survey_questions = $surveyQuestions; // Ordered properly

		$surveys = SurveyCollection::getAllSurveysForAllStarbars($this->single_starbar_id);
		$this->view->surveys = $surveys;

		$reportCells = new ReportCellCollection();
		$reportCells->loadAllReportCells($this->single_starbar_id);
		$this->view->report_cells = $reportCells;
	}


	/*public function surveyPdfExportAction () {
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
	}*/

	public function surveyCsvExportAction () {
		// increase memory limit for this session only
		ini_set('memory_limit', '512M');

		$request = $this->getRequest();
		$surveyId = (int) $request->getParam("survey_id", 0);
		$reportCellId = (int) $request->getParam("report_cell_id", 1);
		$surveyType = $request->getParam("survey_type");
		$starbarId = (int) $request->getParam("starbar_id");

		$surveyQuestions = null;
		$calculationArray = array();
		$survey = new Survey();
		$reportCell = new ReportCell();

		if ($surveyId) {
			$survey->loadData($surveyId);
		}

		if ($reportCellId) {
			$reportCell->loadData($reportCellId);
		}

		if ($survey->id && $reportCell->id) {
			$reportCellSurvey = new ReportCell_Survey();
			$reportCellSurvey->loadDataByUniqueFields(array("report_cell_id" => $reportCellId, "survey_id" => $surveyId));

			// Survey/report cell combination has never been processed before (or was deleted)
			if (!$reportCellSurvey->id) {
				$reportCellSurvey->report_cell_id = $reportCellId;
				$reportCellSurvey->survey_id = $surveyId;
				$reportCellSurvey->save();
			}

			if ($reportCellSurvey->id) {
				// Survey has been taken since the last time it was processed, so re-process it
				if ($reportCellSurvey->last_processed < $survey->last_response) {
					$reportCellSurvey->process();
				}

				$surveyQuestions = new Survey_QuestionCollection();
				$surveyQuestions->loadAllQuestionsForSurvey($surveyId);

				// Place survey questions into an array, where the key is the survey_question_id
				foreach ($surveyQuestions as $surveyQuestion) {
					$calculationArray[$surveyQuestion->id] = array();
				}

				$surveyQuestionChoices = new Survey_QuestionChoiceCollection();
				$surveyQuestionChoices->loadAllChoicesForSurvey($surveyId);

				foreach ($surveyQuestionChoices as $surveyQuestionChoice) {
					$surveyQuestions[$surveyQuestionChoice->survey_question_id]->option_array[$surveyQuestionChoice->id] = $surveyQuestionChoice;
				}

				$reportCellSurveyCalculations = new ReportCell_SurveyCalculationCollection();
				$reportCellSurveyCalculations->loadAllCalculationsForReportCellSurvey($reportCellSurvey->id);

				foreach ($reportCellSurveyCalculations as $reportCellSurveyCalculation) {
					switch ($reportCellSurveyCalculation->parent_type) {
						case "survey_question":
							$calculationArray[$reportCellSurveyCalculation->survey_question_id][0] = $reportCellSurveyCalculation;
							break;

						case "survey_question_choice":
							$calculationArray[$reportCellSurveyCalculation->survey_question_id][$reportCellSurveyCalculation->survey_question_choice_id] = $reportCellSurveyCalculation;
							break;
					}
				}

				$csvHeader = array();
				$csvEmptyRow = array();

				// Prepare arrays for CSV
				foreach ($surveyQuestions AS $surveyQuestion) {
					$questionCalculation = $calculationArray[$surveyQuestion->id][0];
					if ($questionCalculation->number_of_responses) {
						if ($surveyQuestion->piped_from_survey_question_id) {
							$surveyQuestion->option_array = $surveyQuestions[$surveyQuestion->piped_from_survey_question_id]->option_array;
						}
						if (count($surveyQuestion->option_array)) {
							foreach ($surveyQuestion->option_array as $surveyQuestionChoice) {
								$csvHeader[$surveyQuestion->id . '-' . $surveyQuestionChoice->id] = "\"" . str_replace("\"", "\\\"", $surveyQuestion->title . ' - ' . $surveyQuestionChoice->title) . "\"";
								$csvEmptyRow[$surveyQuestion->id . '-' . $surveyQuestionChoice->id] = false;
							}
						} else {
							$csvHeader[$surveyQuestion->id . '-' . 0] = "\"" . str_replace("\"", "\\\"", $surveyQuestion->title) . "\"";
							// Use false so isset() below (line ~1235) returns true if the row exists, so we don't skip columns, and we don't insert columns that don't exist
							$csvEmptyRow[$surveyQuestion->id . '-' . 0] = false;
						}
					}
				}

				// Grab CSV responses from DB

				$joinClause = "";
				if ($reportCell->id > 1) {
					// add to $sql
					$joinClause = " INNER JOIN report_cell_user_map rcum ON rcum.user_id = sr.user_id AND rcum.report_cell_id = ". $reportCell->id . " ";
				} else {
					$joinClause = " INNER JOIN user u ON sr.user_id = u.id AND u.type != 'test' ";
				}
				$surveyResponsesSql = "
					SELECT sr.user_id AS user_id, GROUP_CONCAT(CONCAT(sqr.survey_question_id, ',', IFNULL(sqr.survey_question_choice_id, 0), ',\"', IFNULL(sqr.response_csv, ''), '\"')) AS user_response
					FROM survey_question_response sqr
					INNER JOIN survey_response sr
						ON sqr.survey_response_id = sr.id
					INNER JOIN survey_question sq
						ON sqr.survey_question_id = sq.id
						AND sq.survey_id = ?
					" . $joinClause . "
					GROUP BY sqr.survey_response_id
					ORDER BY sr.user_id
				";
				$surveyResponsesData = Db_Pdo::fetchAll($surveyResponsesSql, $surveyId);

				// Survey responses returned by DB are in the format A,B,C,A,B,C,A,B,C,... for each response
				// (i.e. all responses for one survey for one user in one string)
				// A = question_id
				// B = choice_id (0 for none)
				// C = "csv value of response" (between quotes)
				if ($surveyResponsesData) {
					$pstTime = new DateTime("now", new DateTimeZone('PDT'));
					$filename = preg_replace("/[,.]+/", "", $pstTime->format("Ymd-Hi") . " ". $survey->title . " - " . $reportCell->title);
					$filename .= ".csv";

					// HTTP Header for CSV file
					header("Content-type: text/csv");
					header("Content-Disposition: attachment; filename=".$filename);
					header("Pragma: no-cache");
					header("Expires: 0");

					// CSV Header Row
					echo "User ID," . implode(',', $csvHeader) . "\n";

					foreach ($surveyResponsesData as $surveyResponse) {
						$userId = $surveyResponse['user_id'];
						$responseArray = str_getcsv($surveyResponse['user_response']);
						$numberOfResponses = count($responseArray);
						$csvRow = $csvEmptyRow;

						$i = 0;
						if ($numberOfResponses && ($numberOfResponses % 3) == 0) {
							// prepare the csv line/row
							while ($i < $numberOfResponses) {
								// A,B,C,A,B,C,A,B,C,...
								// $responseArray[$i]     A = question_id
								// $responseArray[$i+1]   B = choice_id (0 for none)
								// $responseArray[$i+2]   C = "csv value of response" (between quotes)
								if (isset($csvRow[$responseArray[$i] . '-' . $responseArray[$i+1]]))
									$csvRow[$responseArray[$i] . '-' . $responseArray[$i+1]] = "\"" . $responseArray[$i+2] . "\"";
								$i += 3;
							}
							// echo the csv line
							echo $userId . ',' . implode(',', $csvRow) . "\n";
						}
					}

					exit;
				}
			}
		}
	}


	public function surveyQuestionResponsesAction () {
		$request = $this->getRequest();
		$reportCellId = (int) $request->getParam("report_cell_id", false);
		$surveyQuestionId = (int) $request->getParam("survey_question_id", false);

		$reportCell = new ReportCell();
		$surveyQuestion = new Survey_Question();

		if ($reportCellId) {
			$reportCell->loadData($reportCellId);
		}

		if ($surveyQuestionId) {
			$surveyQuestion->loadData($surveyQuestionId);
		}

		if ($reportCell->id && $surveyQuestion->id) {
			$surveyQuestion->loadAllResponses($reportCell->id);
			$this->view->responses = $surveyQuestion->response_array;
		} else {
			$this->view->responses = array();
		}
	}


	public function orderHistoryAction() {
		$request = $this->getRequest();
		$weeksAgo = (int) $request->getParam('weeks_ago');
		$this->view->weeks_ago = $weeksAgo;

		$this->view->readable_date_format = "l Y-m-d \\a\\t H:i:s";
		$starbarId = (int) $request->getParam('starbar_id');
		$this->view->hide_tokens = (int) $request->getParam('hide_tokens');
		$this->view->hide_physicals = (int) $request->getParam('hide_physicals');

		if ($starbarId) {
			$starbar = new Starbar();
			$starbar->loadData($starbarId);
			$request->setParam('user_id', 1);
			$game = Game_Starbar::getInstance();

			$this->view->starbar_id = $starbar->id;

			list($this->view->start_date, $this->view->end_date, $this->view->orders, $this->view->goods, $this->view->gamers, $this->view->emails) = GamerOrderHistoryCollection::getOrderHistory($starbarId, $game, $weeksAgo, $this->view->readable_date_format);
		}

		$sql = "SELECT *
				FROM starbar
				WHERE id > 1
				ORDER BY id
				";
		$starbars = Db_Pdo::fetchAll($sql);

		$this->view->starbars = $starbars;
	}


	public function contentEditorAction() {
		$this->view->headScript()->appendFile('//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js');
		$this->view->headScript()->appendFile('//ajax.googleapis.com/ajax/libs/jqueryui/1.10.0/jquery-ui.min.js');
		$this->view->headScript()->appendFile('/js/devadmin/content-editor.js');
		$this->view->headLink()->appendStylesheet('/css/devadmin/content-editor.css');

		Api_Cache::quickRemove('Starbar_Content_Starbar_Id_Index');
		Api_Cache::quickRemove('Starbar_Content_Content_Key_Index');
		Api_Cache::quickRemove('Starbar_Content_Keys');
		$this->view->starbar_content = Starbar_ContentCollection::getAllContent();
		$this->view->keys = Starbar_ContentKeyCollection::getAllKeys();

		$sql = "SELECT *
				FROM starbar
				WHERE id > 2
				";
		$starbars = Db_Pdo::fetchAll($sql);
		array_unshift($starbars, array('id' => '-1', 'label' => 'Default'));
		$this->view->starbars = $starbars;
	}

	public function everyFiveMinutesAction() {
		$this->view->messages = Survey_ResponseCollection::processAllResponsesPendingProcessing();

		ReportCellCollection::processAllReportCellConditions();
		$this->view->messages = array_merge($this->view->messages, array("Report Cell Processing Complete!"));

		quicklog(implode("\n", $this->view->messages));
	}


	public function everyHourAction () {
		$this->view->messages = array("Nothing to do!");
	}


	public function testAction () {
		Log_Event::removeAssetCache();
		$json = trim('
			{
				"base_ts": "'.time().'",
				"events": [
					{
						"type": "page_view",
						"ts": "'.(time() - 25).'",
						"url": "https://www.gmail.com/?classic=1",
						"events": [
							{
								"type": "social_action",
								"ts": "'.(time() - 20).'",
								"social_network": "Twitter",
								"action": "Share",
								"message": "I really love moo.com!!!"
							},
							{
								"type": "asset",
								"ts": "'.(time() - 15).'",
								"provider": "youtube",
								"asset_type": "video",
								"action": "load",
								"asset_id": "QVs_yLZ3X0g",
								"props": [
									{ "title" : "video_url", "url" : "https://www.youtube.com/watch?v=QVs_yLZ3X0g&list=PLZLTS4u9M_2rPFsdbdY7xL8oAApgU0Zar&index=3" },
									{ "title" : "uploader", "category_id" : "machinima", "category_title" : "Machinima" },
									{ "title" : "video_length", "value" : "69" },
									{ "title" : "title", "value" : "Grand Theft Auto 5 -- Trevor Trailer" },
									{ "title" : "playlist_id", "value" : "PLZLTS4u9M_2rPFsdbdY7xL8oAApgU0Zar" },
									{ "title" : "playlist_index", "value" : "3" }
								]
							},
							{
								"type": "social_action",
								"ts": "'.(time() - 6).'",
								"target_url": "http://www.moo.com/?cow=yes&h=&r",
								"social_network": "Facebook",
								"action": "Like"
							},
							{
								"type": "search",
								"ts": "'.(time() - 5).'",
								"query": "Where is waldo?",
								"engine": "Google"
							}
						]
					},
					{
						"type": "search",
						"ts": "'.(time() - 4).'",
						"query": "\"That\'s terrible, Waldo!\", shrieked the nice witch.",
						"engine": "Google"
					}
 				]
			}
		');

		$logEvent = new Log_Event(1, 1);
		$logEvent->insert(json_decode($json, true));
		exit;
	}
}
