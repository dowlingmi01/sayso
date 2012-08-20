<?php

class GamerOrderHistoryCollection extends RecordCollection
{
	static public function getOrderHistory($starbarId = 0, $game = null, $weeksAgo = 0, $readableDateFormat = "l Y-m-d H:i:s") {
		if (!$starbarId || !$game) return;

		$economy = $game->getEconomy();
		$cache = Api_Cache::getInstance('BigDoor_getNamedTransactionGroup_store_' . $economy->getKey(), Api_Cache::LIFETIME_WEEK);
		if ($cache->test()) {
			$data = $cache->load();
		} else {
			$client = $game->getHttpClient();
			$client->setCustomParameters(array(
				'attribute_friendly_id' => 'bdm-product-variant',
				'verbosity' => 9,
				'max_records' => 100
			));
			$client->getNamedTransactionGroup('store');
			$data = $client->getData();
			// leave cache saving to (api) GamingController.php
			// $cache->save($data);
		}

		$allGoods = new ItemCollection();
		foreach ($data as $goodData) {
			$good = new Gaming_BigDoor_Good();
			$good->setPrimaryCurrencyId($game->getPurchaseCurrencyId());
			$good->setGame($game);
			$good->build($goodData);
			$allGoods[] = $good;
		}


		$weeksAgo = (int) abs($weeksAgo);

		$today = strtotime('today');
		$daysSinceMonday = date('N') - 1;

		if ($daysSinceMonday) $startingMonday = strtotime('last monday');
		else $startingMonday = $today;

		if ($weeksAgo) $startingMonday = strtotime('-'.$weeksAgo.' weeks', $startingMonday);

		$endingMonday = strtotime('+1 week', $startingMonday);

		$sqlDateFormat = "Y-m-d H:i:s";
		$sqlStartingDate = date($sqlDateFormat, $startingMonday);
		$readableStartingDate = date($readableDateFormat, $startingMonday);
		$sqlEndingDate = date($sqlDateFormat, $endingMonday);
		$readableEndingDate = date($readableDateFormat, strtotime('-1 second', $endingMonday));

		$orderSql = "
			SELECT ugoh.*
			FROM user_gaming_order_history ugoh
			INNER JOIN user_gaming ug
				ON ugoh.user_gaming_id = ug.id
				AND ug.starbar_id = ?
			WHERE ugoh.created >= ?
				AND ugoh.created < ?
		";

		$ordersData = Db_Pdo::fetchAll($orderSql, $starbarId, $sqlStartingDate, $sqlEndingDate);

		$orders = new GamerOrderHistoryCollection();
		$orders->build($ordersData, new GamerOrderHistory());

		if (!(sizeof($orders))) return array ($readableStartingDate, $readableEndingDate, null, null, null, null);


		$goods = new ItemCollection();
		$listOfGamerIds = "";

		foreach ($orders as $order) {
			if (!isset($goods[$order->good_id])) {
				$goods[$order->good_id] = $allGoods->getItem($order->good_id);
			}

			if ($listOfGamerIds) $listOfGamerIds .= ",";
			$listOfGamerIds .= $order->user_gaming_id;
		}


		$gamerSql = "
			SELECT *
			FROM user_gaming
			WHERE id IN (".$listOfGamerIds.")
		";
		$gamerData = Db_Pdo::fetchAll($gamerSql);

		$gamers = new RecordCollection();
		$gamers->build($gamerData, new Gamer());


		$emailSql = "
			SELECT e.*
			FROM user_email e
			INNER JOIN user_gaming g
				ON e.user_id = g.user_id
				AND g.id IN (".$listOfGamerIds.")
		";
		$emailData = Db_Pdo::fetchAll($emailSql);

		$emails = new RecordCollection();
		$emails->build($emailData, new User_Email());

		return array($readableStartingDate, $readableEndingDate, $orders, $goods, $gamers, $emails);
	}
}
