<?php
/**
 * Starbar actions in this controller are for local testing,
 * using an environment (via actions/views) that mimics the browser app.
 * Each view brings in the Remote equivalent via partial()
 *
 * @see RemoteController for actual Starbars
 * @author davidbjames
 */
require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Starbar_IndexController extends Api_GlobalController
{
	public function preDispatch() {
		// for the simulated app, we also have to simulate passing
		// a user_key from the "client". This only applies here for the
		// initial load. Other actions (ajax, etc) will still need to
		// pass user_key, which can be retreived via sayso.starbar.user.key
		if (!$this->user_id || !$this->user_key) {
			$this->user_id = 1;
			if (isset($_COOKIE['simulated_starbar_user_key'])) {
				$this->user_key = $_COOKIE['simulated_starbar_user_key'];
			} else {
				$this->user_key = User::getHash($this->user_id);
				setcookie('simulated_starbar_user_key', $this->user_key);
			}
		}
		Api_UserSession::getInstance($this->user_key)->setId($this->user_id);
		if (!in_array($this->_request->getActionName(), array('index', 'gaga'))) {
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
	}

	public function indexAction () {

	}

	public function gagaAction()
	{
		$this->view->headLink()->appendStylesheet('/css/starbar-qualified.css');
		$this->view->headScript()->appendFile('/js/starbar/jquery-1.7.1.min.js');
	}

	public function genericAction () {
	}

	public function userPageViewTrackingReportAction () {
		// Starbar
		$starbar = new Starbar();
		$starbar->loadDataByUniqueFields(array('short_name' => 'hellomusic'));
		$starbar->setVisibility('stowed');
		$this->view->starbar = $starbar;
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
		// Starbar
		$starbar = new Starbar();
		$starbar->loadDataByUniqueFields(array('short_name' => 'hellomusic'));
		$starbar->setVisibility('stowed');
		$this->view->starbar = $starbar;
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
		// Starbar
		$starbar = new Starbar();
		$starbar->loadDataByUniqueFields(array('short_name' => 'hellomusic'));
		$starbar->setVisibility('stowed');
		$this->view->starbar = $starbar;

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
		// Starbar
		$starbar = new Starbar();
		$starbar->loadDataByUniqueFields(array('short_name' => 'hellomusic'));
		$starbar->setVisibility('stowed');
		$this->view->starbar = $starbar;

		$notesReportCSV = "user_id,email,notes_remaining,first_name,last_name\n";

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

			foreach ($gamerInfo->currency_balances as $currency) {
				if ((strtolower($currency->end_user_title) == 'notes' || strtolower($currency->pub_title) == 'notes') && intval($currency->current_balance)) {
					$notesReportCSV .= $gamer['user_id'] . ',' . $gamer['email'] . ',' . intval($currency->current_balance) . ',' . $gamer['first_name'] . ',' . $gamer['last_name'] . "\n";
				}
			}
		}

		$this->view->notesReportCSV = $notesReportCSV;
	}

	public function inventoryAction () {
		// Starbar
		$starbar = new Starbar();
		$starbar->loadDataByUniqueFields(array('short_name' => 'hellomusic'));
		$starbar->setVisibility('stowed');
		$this->view->starbar = $starbar;

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
		// Starbar
		$starbar = new Starbar();
		$starbar->loadDataByUniqueFields(array('short_name' => 'hellomusic'));
		$starbar->setVisibility('stowed');
		$this->view->starbar = $starbar;

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

	public function hellomusicAction () {
		$this->view->headLink()->appendStylesheet('/css/starbar-hellomusic.css');

		$this->view->inlineScript()->appendFile('/js/starbar/starbar-new.js');

		// Starbar
		$starbar = new Starbar();
		$starbar->loadDataByUniqueFields(array('short_name' => 'hellomusic'));
		$starbar->setVisibility('open');
		$this->view->starbar = $starbar;

		// User
		$session = Api_UserSession::getInstance($this->user_key);
		$user = $session->getUser();
		$this->view->user = $user;

		// Facebook Connection
		$facebookSocial = new User_Social();
		$facebookSocial->loadByUserIdAndProvider($user->id, 'facebook');
		$this->view->assign('facebook_social', $facebookSocial);

		// Gamer
		// make sure this user (and session) has a gaming user associated
		$gamer = Gamer::create($user->getId(), $starbar->getId());
		$session->setGamingUser($gamer);

		$this->_request->setParam('starbar_id', $starbar->getId());
		$game = Game_Starbar::getInstance();
		$this->view->assign('game', $game);

		if ($this->install) {
			$game->install();
		}
	}
}
