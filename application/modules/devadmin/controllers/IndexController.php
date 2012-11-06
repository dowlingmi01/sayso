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
				WHERE id > 1
				";
		$starbars = Db_Pdo::fetchAll($sql);
		$this->view->starbars = $starbars;

		if ($starbarId) {
			$starbar = new Starbar();
			$starbar->loadData($starbarId);
			$request->setParam('user_id', 1);
			$gameStarbar = Game_Starbar::getInstance();

			$this->view->starbar_id = $starbar->id;
			$economy = $gameStarbar->getEconomy();

			$client = $economy->getClient();
			$namedGoodCollectionId = $economy->getGoodId("NAMED_GOOD_COLLECTION");

			$client->getNamedGoodCollection($namedGoodCollectionId);
			$data = $client->getData();
			$unfilteredGoods = $data->named_goods;
			$goods = array();

			// Keep only items for this environment
			foreach ($unfilteredGoods as $good) {
				if (strpos($good->end_user_title, ' (Variant)') !== false) {
					foreach ($good->attributes as $attribute) {
						if ($attribute->friendly_id == "environment-".APPLICATION_ENV) {
							$goods[] = $good;
							continue 2; // Go to next unfiltered good
						}
					}
				}
			}

			// Filter out tokens
			foreach ($goods as $goodIndex => $good) {
				foreach ($good->attributes as $attribute) {
					if ($attribute->friendly_id == "giveaway-token") {
						unset($goods[$goodIndex]);
					}
				}
			}

			$remainingInventory = "N/A";
			$soldInventory = "N/A (probably)";

			if ($goodId) {
				$client->namedGoodCollection($namedGoodCollectionId)->namedGood($goodId)->getInventory();
				$data = $client->getData();
				if ($data) {
					$soldInventory = $data->sold_inventory;
					$remainingInventory = $data->total_inventory - $soldInventory;
					if ($newInventory != "") {
						$newInventory = abs($newInventory);
						$remainingInventory = $newInventory;
						$client->setParameterPost('total_inventory', $remainingInventory+$soldInventory);
						$client->namedGoodCollection($namedGoodCollectionId)->namedGood($goodId)->putInventory();

						$game = Game_Starbar::getInstance();
						$cache = Api_Cache::getInstance('BigDoor_getNamedTransactionGroup_store_' . $economy->getKey(), Api_Cache::LIFETIME_WEEK);
						$cache->remove();
						// To avoid reloading the form and setting the inventory again
						$this->_redirect("/devadmin/index/inventory?starbar_id=".$starbarId."&named_good_id=".$goodId);
					}
				} else {
					if ($newInventory != "") {
						$newInventory = abs($newInventory);
						$remainingInventory = $newInventory;
						$client->setParameterPost('total_inventory', $remainingInventory);
						$client->namedGoodCollection($namedGoodCollectionId)->namedGood($goodId)->postInventory(); // post CREATES inventory

						$game = Game_Starbar::getInstance();
						$cache = Api_Cache::getInstance('BigDoor_getNamedTransactionGroup_store_' . $economy->getKey(), Api_Cache::LIFETIME_WEEK);
						$cache->remove();
						// To avoid reloading the form and setting the inventory again
						$this->_redirect("/devadmin/index/inventory?starbar_id=".$starbarId."&named_good_id=".$goodId);
					}
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
				WHERE id > 1
				";
		$starbars = Db_Pdo::fetchAll($sql);
		$this->view->starbars = $starbars;

		if ($starbarId) {
			$starbar = new Starbar();
			$starbar->loadData($starbarId);
			$request->setParam('user_id', 1);
			$gameStarbar = Game_Starbar::getInstance();

			$this->view->starbar_id = $starbar->id;
			$economy = $gameStarbar->getEconomy();
		}

		if ($operation == "add") {
			// User-entered data
			$productTitle = $request->getParam('product_title');
			$initialInventory = (int) abs($request->getParam('initial_inventory'));
			$imageUrlFull = $request->getParam('image_url_full');
			$imageUrlPreview = $request->getParam('image_url_preview');
			$imageUrlPreviewBought = $request->getParam('image_url_preview_bought');
			$price = (int) abs($request->getParam('price'));
			$type = $request->getParam('type');

			// Definitions
			$client = $economy->getClient();
				// $client = new Gaming_BigDoor_HttpClient('43bfbce697bd4be99c9bf276f9c6b086', '35eb12f3e87144a0822cf1d18d93d867'); // Snakkle
				// $client = new Gaming_BigDoor_HttpClient('2107954aa40c46f090b9a562768b1e18', '76adcb0c853f486297933c34816f1cd2'); // Hello Music
				/*$client->getNamedTransactionGroup(5330003);
				do_dump($client->getData(), "Transaction Group for Product");
				$client->getNamedTransactionGroup(5342008);
				do_dump($client->getData(), "Transaction Group for Product Variant");

				$client->setCustomParameters(array(
					'attribute_friendly_id' => 'bdm-product-variant',
					'verbosity' => 9,
					'max_records' => 100
				));
				$client->getNamedTransactionGroup('store');
				$data = $client->getData();
				do_dump($data, 'data'); exit;*/

			$attributeFullId = $economy->getAttributeId("FULL_STORE");
			$attributePreviewId = $economy->getAttributeId("PREVIEW_STORE");
			$attributePreviewBoughtId = $economy->getAttributeId("PREVIEW_BOUGHT");
			$attributeTokenId = $economy->getAttributeId("TOKEN");

			$attributeProductId = $economy->getAttributeId("PRODUCT");
			$attributeProductVariantId = $economy->getAttributeId("PRODUCT_VARIANT");

			$namedGoodCollectionId = $economy->getGoodId("NAMED_GOOD_COLLECTION");
			$currencyPurchasePointsId = $economy->getCurrencyId("PURCHASE_POINTS");
			$currencyTokenPointsId = $economy->getCurrencyId("TOKEN_POINTS");
			$currencyRedeemableId = $economy->getCurrencyIdByType("redeemable");

			$attributeEnvironmentId = $economy->getAttributeId("ENVIRONMENT_" . strtoupper(APPLICATION_ENV));

			if ($operation == "add") {
				set_time_limit(300);

				// // // // // ADD PRODUCT

				// // // Add Association for Product
				$client->setParameterPost("end_user_description", $productTitle . " (Product Association)");
				$client->setParameterPost("pub_title", $productTitle . " (Product Association)");
				$client->setParameterPost("pub_description", $productTitle . " (Product Association)");
				$client->setParameterPost("end_user_title", $productTitle . " (Product Association)");
				$client->postAttribute();
				$data = $client->getData();
				$attributeProductAssociationId = $data->id;

				$client->attribute($attributeProductId)->postAttribute($attributeProductAssociationId);


				// // // Add Named Good for Product
				$client->setParameterPost("end_user_description", $productTitle);
				$client->setParameterPost("pub_title", $productTitle);
				$client->setParameterPost("pub_description", $productTitle);
				$client->setParameterPost("end_user_title", $productTitle);
				$client->setParameterPost("read_only", 0);
				$client->namedGoodCollection($namedGoodCollectionId)->postNamedGood();

				$data = $client->getData();
				$namedGoodProductId = $data->id;

				$client->attribute($attributeProductId)->postNamedGood($namedGoodProductId);
				$client->attribute($attributeProductAssociationId)->postNamedGood($namedGoodProductId);

				// // // Add Named Transactions for Product
				// Purchasing Points Transaction
				$client->setParameterPost("end_user_description", "GOOD Product Transaction (Purchasing Points): " . $productTitle);
				$client->setParameterPost("pub_title", "GOOD Product Transaction (Purchasing Points): " . $productTitle);
				$client->setParameterPost("pub_description", "GOOD Product Transaction (Purchasing Points): " . $productTitle);
				$client->setParameterPost("end_user_title", "GOOD Product Transaction (Purchasing Points): " . $productTitle);
				$client->setParameterPost("read_only", 0);
				$client->setParameterPost("named_transaction_group_ratio", "-1.00");
				$client->setParameterPost("is_source", 0);
				$client->setParameterPost("variable_amount_allowed", 1);
				$client->setParameterPost("is_multi_user", 0);
				$client->setParameterPost("named_transaction_is_primary", 0);
				$client->setParameterPost("notifiable_event", 0);
				$client->setParameterPost("currency_id", $currencyPurchasePointsId);
				$client->setParameterPost("default_amount", "1.00");
				$client->postNamedTransaction();

				$data = $client->getData();
				$namedTransactionProductPurchasingPointsId = $data->id;

				// Redeemable Points Transaction
				$client->setParameterPost("end_user_description", $productTitle . " (Full)");
				$client->setParameterPost("pub_title", $productTitle . " (Full)");
				$client->setParameterPost("pub_description", $productTitle . " (Full)");
				$client->setParameterPost("end_user_title", $productTitle . " (Full)");
				$client->setParameterPost("read_only", 0);
				$client->setParameterPost("named_transaction_group_ratio", "-1.00");
				$client->setParameterPost("is_source", 0);
				$client->setParameterPost("variable_amount_allowed", 1);
				$client->setParameterPost("is_multi_user", 0);
				$client->setParameterPost("named_transaction_is_primary", 1);
				$client->setParameterPost("notifiable_event", 0);
				$client->setParameterPost("currency_id", $currencyRedeemableId);
				$client->setParameterPost("default_amount", "-" . $price . ".00");
				$client->setParameterPost("named_good_id", $namedGoodProductId);
				$client->postNamedTransaction();

				$data = $client->getData();
				$namedTransactionProductRedeemablePointsId = $data->id;

				// Token Points Transaction
				if ($type == "token") {
					$client->setParameterPost("end_user_description", "GOOD Product Transaction (Token Points): " . $productTitle);
					$client->setParameterPost("pub_title", "GOOD Product Transaction (Token Points): " . $productTitle);
					$client->setParameterPost("pub_description", "GOOD Product Transaction (Token Points): " . $productTitle);
					$client->setParameterPost("end_user_title", "GOOD Product Transaction (Token Points): " . $productTitle);
					$client->setParameterPost("read_only", 0);
					$client->setParameterPost("named_transaction_group_ratio", "-1.00");
					$client->setParameterPost("is_source", 0);
					$client->setParameterPost("variable_amount_allowed", 1);
					$client->setParameterPost("is_multi_user", 0);
					$client->setParameterPost("named_transaction_is_primary", 0);
					$client->setParameterPost("notifiable_event", 0);
					$client->setParameterPost("currency_id", $currencyTokenPointsId);
					$client->setParameterPost("default_amount", "1.00");
					$client->postNamedTransaction();

					$data = $client->getData();
					$namedTransactionProductTokenPointsId = $data->id;
				}

				// // // Add Named Transaction Group for Product
				$client->setParameterPost("end_user_description", "BUY " . $productTitle);
				$client->setParameterPost("pub_title", "BUY " . $productTitle);
				$client->setParameterPost("pub_description", "BUY " . $productTitle);
				$client->setParameterPost("end_user_title", "BUY " . $productTitle);
				if ($type == "token") $client->setParameterPost("end_user_cap", -1);
				else $client->setParameterPost("end_user_cap", 1);
				$client->setParameterPost("end_user_cap_interval", -1);
				$client->setParameterPost("read_only", 0);
				$client->setParameterPost("challenge_response_enabled", 0);
				$client->setParameterPost("non_secure", 1);
				$client->setParameterPost("requires_end_user_auth", 0);
				$client->postNamedTransactionGroup();

				$data = $client->getData();
				$namedTransactionGroupProductId = $data->id;

				$client->namedTransactionGroup($namedTransactionGroupProductId)->postNamedTransaction($namedTransactionProductPurchasingPointsId);
				$client->setParameterPost("named_transaction_is_primary", 1);
				$client->namedTransactionGroup($namedTransactionGroupProductId)->postNamedTransaction($namedTransactionProductRedeemablePointsId);
				if ($type == "token") {
					$client->namedTransactionGroup($namedTransactionGroupProductId)->postNamedTransaction($namedTransactionProductTokenPointsId);
				}

				// // // // // ADD PRODUCT VARIANT

				// // // Add URLs
				// Full Image
				$client->setParameterPost("end_user_description", "GOOD Full Image: " . $productTitle);
				$client->setParameterPost("pub_title", "GOOD Full Image: " . $productTitle);
				$client->setParameterPost("pub_description", "GOOD Full Image: " . $productTitle);
				$client->setParameterPost("end_user_title", "GOOD Full Image: " . $productTitle);
				$client->setParameterPost("url", $imageUrlFull);
				$client->setParameterPost("read_only", 0);
				$client->setParameterPost("is_media_url", 0);
				$client->setParameterPost("is_for_end_user_ui", 0);
				$client->postUrl();

				$data = $client->getData();
				$imageUrlFullId = $data->id;

				$client->attribute($attributeFullId)->postUrl($imageUrlFullId);

				// Preview Image
				$client->setParameterPost("end_user_description", "GOOD Preview Image: " . $productTitle);
				$client->setParameterPost("pub_title", "GOOD Preview Image: " . $productTitle);
				$client->setParameterPost("pub_description", "GOOD Preview Image: " . $productTitle);
				$client->setParameterPost("end_user_title", "GOOD Preview Image: " . $productTitle);
				$client->setParameterPost("url", $imageUrlPreview);
				$client->setParameterPost("read_only", 0);
				$client->setParameterPost("is_media_url", 0);
				$client->setParameterPost("is_for_end_user_ui", 0);
				$client->postUrl();

				$data = $client->getData();
				$imageUrlPreviewId = $data->id;

				$client->attribute($attributePreviewId)->postUrl($imageUrlPreviewId);

				// Preview-Bought Image
				$client->setParameterPost("end_user_description", "GOOD Preview-Bought Image: " . $productTitle);
				$client->setParameterPost("pub_title", "GOOD Preview-Bought Image: " . $productTitle);
				$client->setParameterPost("pub_description", "GOOD Preview-Bought Image: " . $productTitle);
				$client->setParameterPost("end_user_title", "GOOD Preview-Bought Image: " . $productTitle);
				$client->setParameterPost("url", $imageUrlPreviewBought);
				$client->setParameterPost("read_only", 0);
				$client->setParameterPost("is_media_url", 0);
				$client->setParameterPost("is_for_end_user_ui", 0);
				$client->postUrl();

				$data = $client->getData();
				$imageUrlPreviewBoughtId = $data->id;

				$client->attribute($attributePreviewBoughtId)->postUrl($imageUrlPreviewBoughtId);

				// // // Add Association for Product Variant
				$client->setParameterPost("end_user_description", $productTitle . " (Product Variant Association)");
				$client->setParameterPost("pub_title", $productTitle . " (Product Variant Association)");
				$client->setParameterPost("pub_description", $productTitle . " (Product Variant Association)");
				$client->setParameterPost("end_user_title", $productTitle . " (Product Variant Association)");
				$client->postAttribute();
				$data = $client->getData();
				$attributeProductVariantAssociationId = $data->id;

				//$client->attribute($attributeProductVariantId)->postAttribute($attributeProductVariantAssociationId);
				$client->attribute($attributeProductId)->postAttribute($attributeProductVariantAssociationId);


				// // // Add Named Good for Product Variant
				$client->setParameterPost("end_user_description", $productTitle . " (Variant)");
				$client->setParameterPost("pub_title", $productTitle . " (Variant)");
				$client->setParameterPost("pub_description", $productTitle . " (Variant)");
				$client->setParameterPost("end_user_title", $productTitle . " (Variant)");
				$client->setParameterPost("read_only", 0);
				$client->namedGoodCollection($namedGoodCollectionId)->postNamedGood();

				$data = $client->getData();
				$namedGoodProductVariantId = $data->id;

				$client->attribute($attributeProductVariantId)->postNamedGood($namedGoodProductVariantId);
				$client->attribute($attributeProductVariantAssociationId)->postNamedGood($namedGoodProductVariantId);
				$client->attribute($attributeEnvironmentId)->postNamedGood($namedGoodProductVariantId);
				$client->url($imageUrlFullId)->postNamedGood($namedGoodProductVariantId);
				$client->url($imageUrlPreviewId)->postNamedGood($namedGoodProductVariantId);
				$client->url($imageUrlPreviewBoughtId)->postNamedGood($namedGoodProductVariantId);

				if ($type == "token") {
					$client->attribute($attributeTokenId)->postNamedGood($namedGoodProductVariantId);
				} else {
					if (!$initialInventory) $initialInventory = 1;
					$client->setParameterPost('total_inventory', $initialInventory);
					$client->namedGoodCollection($namedGoodCollectionId)->namedGood($namedGoodProductVariantId)->postInventory();
				}

				// // // Add Named Transactions for Product Variant
				// Purchasing Points Transaction
				$client->setParameterPost("end_user_description", "GOOD Product Variant Transaction (Purchasing Points): " . $productTitle);
				$client->setParameterPost("pub_title", "GOOD Product Variant Transaction (Purchasing Points): " . $productTitle);
				$client->setParameterPost("pub_description", "GOOD Product Variant Transaction (Purchasing Points): " . $productTitle);
				$client->setParameterPost("end_user_title", "GOOD Product Variant Transaction (Purchasing Points): " . $productTitle);
				$client->setParameterPost("read_only", 0);
				$client->setParameterPost("named_transaction_group_ratio", "-1.00");
				$client->setParameterPost("is_source", 0);
				$client->setParameterPost("variable_amount_allowed", 1);
				$client->setParameterPost("is_multi_user", 0);
				$client->setParameterPost("named_transaction_is_primary", 0);
				$client->setParameterPost("notifiable_event", 0);
				$client->setParameterPost("currency_id", $currencyPurchasePointsId);
				$client->setParameterPost("default_amount", "1.00");
				$client->postNamedTransaction();

				$data = $client->getData();
				$namedTransactionProductVariantPurchasingPointsId = $data->id;

				// Redeemable Points Transaction
				$client->setParameterPost("end_user_description", $productTitle . " (Variant)");
				$client->setParameterPost("pub_title", $productTitle . " (Variant)");
				$client->setParameterPost("pub_description", $productTitle . " (Variant)");
				$client->setParameterPost("end_user_title", $productTitle . " (Variant)");
				$client->setParameterPost("read_only", 0);
				$client->setParameterPost("named_transaction_group_ratio", "-1.00");
				$client->setParameterPost("is_source", 0);
				$client->setParameterPost("variable_amount_allowed", 1);
				$client->setParameterPost("is_multi_user", 0);
				$client->setParameterPost("named_transaction_is_primary", 1);
				$client->setParameterPost("notifiable_event", 0);
				$client->setParameterPost("currency_id", $currencyRedeemableId);
				$client->setParameterPost("default_amount", "-" . $price . ".00");
				$client->setParameterPost("named_good_id", $namedGoodProductVariantId);
				$client->postNamedTransaction();

				$data = $client->getData();
				$namedTransactionProductVariantRedeemablePointsId = $data->id;

				$client->attribute($attributeProductVariantId)->postNamedTransaction($namedTransactionProductVariantRedeemablePointsId);

				// Token Points Transaction
				if ($type == "token") {
					$client->setParameterPost("end_user_description", "GOOD Product Variant Transaction (Token Points): " . $productTitle);
					$client->setParameterPost("pub_title", "GOOD Product Variant Transaction (Token Points): " . $productTitle);
					$client->setParameterPost("pub_description", "GOOD Product Variant Transaction (Token Points): " . $productTitle);
					$client->setParameterPost("end_user_title", "GOOD Product Variant Transaction (Token Points): " . $productTitle);
					$client->setParameterPost("read_only", 0);
					$client->setParameterPost("named_transaction_group_ratio", "-1.00");
					$client->setParameterPost("is_source", 0);
					$client->setParameterPost("variable_amount_allowed", 1);
					$client->setParameterPost("is_multi_user", 0);
					$client->setParameterPost("named_transaction_is_primary", 0);
					$client->setParameterPost("notifiable_event", 0);
					$client->setParameterPost("currency_id", $currencyTokenPointsId);
					$client->setParameterPost("default_amount", "1.00");
					$client->postNamedTransaction();

					$data = $client->getData();
					$namedTransactionProductVariantTokenPointsId = $data->id;
				}

				// // // Add Named Transaction Group for Product Variant
				$client->setParameterPost("end_user_description", "BUY " . $productTitle . " (Variant)");
				$client->setParameterPost("pub_title", "BUY " . $productTitle . " (Variant)");
				$client->setParameterPost("pub_description", "BUY " . $productTitle . " (Variant)");
				$client->setParameterPost("end_user_title", "BUY " . $productTitle . " (Variant)");
				if ($type == "token") $client->setParameterPost("end_user_cap", -1);
				else $client->setParameterPost("end_user_cap", 1);
				$client->setParameterPost("end_user_cap_interval", -1);
				$client->setParameterPost("read_only", 0);
				$client->setParameterPost("challenge_response_enabled", 0);
				$client->setParameterPost("non_secure", 1);
				$client->setParameterPost("requires_end_user_auth", 0);
				$client->postNamedTransactionGroup();

				$data = $client->getData();
				$namedTransactionGroupProductVariantId = $data->id;

				$client->namedTransactionGroup($namedTransactionGroupProductVariantId)->postNamedTransaction($namedTransactionProductVariantPurchasingPointsId);
				$client->setParameterPost("named_transaction_is_primary", 1);
				$client->namedTransactionGroup($namedTransactionGroupProductVariantId)->postNamedTransaction($namedTransactionProductVariantRedeemablePointsId);
				if ($type == "token") {
					$client->namedTransactionGroup($namedTransactionGroupProductVariantId)->postNamedTransaction($namedTransactionProductVariantTokenPointsId);
				}

				$client->getNamedTransactionGroup($namedTransactionGroupProductId);
				do_dump($client->getData(), "Transaction Group for Product");
				$client->getNamedTransactionGroup($namedTransactionGroupProductVariantId);
				do_dump($client->getData(), "Transaction Group for Product Variant");

				//$cache = Api_Cache::getInstance('BigDoor_getNamedTransactionGroup_store_' . $game->getEconomy()->getKey(), Api_Cache::LIFETIME_WEEK);
				$cache = Api_Cache::getInstance('BigDoor_getNamedTransactionGroup_store_' . $economy->getKey());
				$cache->remove();

				exit;
			}
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
			$starbar = new Starbar();
			$starbar->loadData($starbarId);
			$request->setParam('user_id', 1);
			$gameStarbar = Game_Starbar::getInstance();

			$this->view->starbar_id = $starbar->id;
			$economy = $gameStarbar->getEconomy();

			$sql = "
				SELECT ugoh.good_id, SUM(ugoh.quantity) AS total_purchased
				FROM user_gaming_order_history ugoh
				INNER JOIN user_gaming ug
					ON ugoh.user_gaming_id = ug.id
					AND ug.starbar_id = ?
				GROUP BY ugoh.good_id
				ORDER BY total_purchased DESC
			";

			$purchasedGoods = Db_Pdo::fetchAll($sql, $starbarId);

			$goodsData = $gameStarbar->getGoodsFromStore();

			$goods = new ItemCollection();

			foreach ($goodsData as $goodData) {
				$good = new Gaming_BigDoor_Good();
				$good->setPrimaryCurrencyId($gameStarbar->getPurchaseCurrencyId());
				$good->build($goodData);
				$goods[(int) $good->getId()] = $good;
			}

			foreach ($purchasedGoods as $purchasedGood) {
				$purchasedGoodId = (int) $purchasedGood['good_id'];
				$totalQuantityPurchased = (int) $purchasedGood['total_purchased'];
				if (isset($goods[$purchasedGoodId])) {
					$good = $goods[$purchasedGoodId];
					$good->setGame($gameStarbar);
					if ($good->isToken()) {
						$tokens[$good->getId()] = array("good" => $good, "total_purchased" => $totalQuantityPurchased);
					}
				}
			}
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
				WHERE good_id = ?
				HAVING cumulative_quantity >= ?
				ORDER BY ugoh.id ASC
				LIMIT 1;
			";
			$winningTransactionResult = Db_Pdo::fetch($sql, $goodId, $randomWinner);

			if ($winningTransactionResult) {
				$this->view->winning_transaction = new GamerOrderHistory($winningTransactionResult);
				$this->view->winning_gamer = new Gamer();
				$this->view->winning_gamer->loadData($this->view->winning_transaction->user_gaming_id);
				$this->view->winning_user = new User();
				if ($this->view->winning_gamer->id && $this->view->winning_gamer->user_id) {
					$this->view->winning_user->loadData($this->view->winning_gamer->user_id);
				}
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
		$this->view->headScript()->appendFile('/js/dig/dig.js');
		$this->view->headLink()->appendStylesheet('/css/dig/dig.css');

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
		$this->view->headScript()->appendFile('/js/dig/dig.js');
		$this->view->headLink()->appendStylesheet('/css/dig/dig.css');

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
		$this->view->headLink()->appendStylesheet('/js/jqplot/jquery.jqplot.min.css');
		$this->view->headScript()->appendFile('//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js');
		$this->view->headScript()->appendFile('/js/dig/dig.js');
		$this->view->headScript()->appendFile('/js/jqplot/jquery.jqplot.min.js');
		$this->view->headScript()->appendFile('/js/jqplot/plugins/jqplot.barRenderer.min.js');
		$this->view->headScript()->appendFile('/js/jqplot/plugins/jqplot.categoryAxisRenderer.min.js');
		$this->view->headScript()->appendFile('/js/jqplot/plugins/jqplot.pointLabels.min.js');

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


	public function everyFiveMinutesAction() {
		$this->view->messages = Survey_ResponseCollection::processAllResponsesPendingProcessing();

		ReportCellCollection::processAllReportCellConditions();
		$this->view->messages = array_merge($this->view->messages, array("Report Cell Processing Complete!"));

		quicklog(implode("\n", $this->view->messages));
	}


	public function everyHourAction () {
		$this->view->messages = array("Nothing to do!");
	}

}
