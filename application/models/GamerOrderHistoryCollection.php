<?php

class GamerOrderHistoryCollection extends RecordCollection
{
	static public function getOrderHistory($starbarId = 0, $weeksAgo = 0, $readableDateFormat = "l Y-m-d H:i:s") {
		if (!$starbarId) return;
		$economyId = Economy::getIdforStarbar($starbarId);
		$goods = Economy::getForId($economyId)->_purchasables;

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
			SELECT ugoh.*, ue.email
			FROM user_gaming_order_history ugoh
			INNER JOIN game_asset ga
				ON ugoh.game_asset_id = ga.id
				AND ga.economy_id = ?
			INNER JOIN user u
			    ON ugoh.user_id = u.id
			INNER JOIN user_email ue
			    ON u.primary_email_id = ue.id
			WHERE ugoh.created >= ?
				AND ugoh.created < ?
		";

		$ordersData = Db_Pdo::fetchAll($orderSql, $economyId, $sqlStartingDate, $sqlEndingDate);

		$orders = new ItemCollection();
		$orders->build($ordersData, new Item());

		if (!(sizeof($orders))) return array ($readableStartingDate, $readableEndingDate, null, null, null, null);

		return array($readableStartingDate, $readableEndingDate, $orders, $goods);
	}
}
