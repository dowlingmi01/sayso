<?
class SummaryReportHack {
	static public function getReportResults($starbarId) {
		if (!$starbarId) return;

		$reportResults = array();
		$reportsToRun = array();

		$user_filter_all = " FIND_IN_SET(u.id, @users_all) ";
		$user_filter_new = " FIND_IN_SET(u.id, @users_new) ";
		$user_filter_active = " FIND_IN_SET(u.id, @users_active) ";
		$user_filter_inactive = " FIND_IN_SET(u.id, @users_inactive) ";
		$user_filter_above_3 = " FIND_IN_SET(u.id, @users_above_3) ";

		// make sure the group_concat_max_len is long enough
		$curMax = (int) Db_Pdo::fetch("SHOW VARIABLES LIKE 'group_concat_max_len'")['Value'];
		// if not, increase it
		if ($curMax < 1000000) Db_Pdo::execute("SET group_concat_max_len = 1000000");

		// set up session variables
		$sql = "
			SET @one_week_ago = (CURRENT_DATE - INTERVAL 1 WEEK);
			SET @three_weeks_ago = (CURRENT_DATE - INTERVAL 3 WEEK);
			SET @two_months_ago = (CURRENT_DATE - INTERVAL 2 MONTH);
			SELECT @users_all := group_concat(distinct(u.id)) FROM user u INNER JOIN user_install i ON i.user_id = u.id AND i.starbar_id = $starbarId WHERE u.type != 'test';
			SELECT @users_new := group_concat(distinct(u.id)) FROM user u WHERE u.created > @three_weeks_ago AND FIND_IN_SET(u.id, @users_all);
			SELECT @mpv_id_three_weeks_ago := id FROM metrics_page_view WHERE created < @three_weeks_ago ORDER BY id DESC LIMIT 1;
			SELECT @users_active := group_concat(user_id) FROM (SELECT DISTINCT user_id FROM metrics_page_view WHERE id > @mpv_id_three_weeks_ago AND FIND_IN_SET(user_id, @users_all)) AS blahblah;
			SELECT @users_inactive := group_concat(distinct(id)) FROM user WHERE NOT FIND_IN_SET(id, @users_active) AND FIND_IN_SET(id, @users_all);
			SELECT @level_asset := a.id FROM game_asset a INNER JOIN economy e ON e.id = a.economy_id INNER JOIN starbar s ON s.economy_id = e.id AND s.id = $starbarId WHERE a.type = 'level';
			SELECT @users_above_3 := group_concat(distinct(u.id)) FROM user u INNER JOIN game_balance b ON u.id = b.user_id AND (b.credits - b.debits) >= 3 AND b.game_asset_id = @level_asset WHERE FIND_IN_SET(u.id, @users_all);
			SELECT @exp_asset := a.id FROM game_asset a INNER JOIN game_currency c ON c.game_asset_id = a.id AND c.game_currency_type_id = 1 INNER JOIN economy e ON e.id = a.economy_id INNER JOIN starbar s ON s.economy_id = e.id AND s.id = $starbarId;
			SELECT @red_asset := a.id FROM game_asset a INNER JOIN game_currency c ON c.game_asset_id = a.id AND c.game_currency_type_id = 2 INNER JOIN economy e ON e.id = a.economy_id INNER JOIN starbar s ON s.economy_id = e.id AND s.id = $starbarId;
		";

		Db_Pdo::execute($sql);

		// set up all the reports
		/*
		$reportsToRun[] = [
			"title" => "debug",
			"single" => " SELECT @users_all AS col1 "
		];
		*/

		$reportsToRun[] = [
			"section_title" => "User Summary",
			"section_description" => "In the table below, <b>New Users</b> means users who have registered less than 3 weeks ago.<br /><b>Active Users</b> refers to users who have had the browser app installed in the past 3 weeks (i.e. behavioral data collected in past 3 weeks).",
		];

		$reportsToRun[] = [
			"title" => "Email addresses collected",
			"total" => "
				SELECT @total_emails := count(distinct(u.id)) AS col1
				FROM user u
				WHERE $user_filter_all
			",
			"new" => "
				SELECT @new_emails := count(distinct(u.id)) AS col1
				FROM user u
				WHERE $user_filter_new
			",
		];

		$reportsToRun[] = [
			"title" => "Installs",
			"total" => "
				SELECT @total_installs := count(distinct(u.id)) AS col1
				FROM user u
				INNER JOIN user_install i
					ON i.user_id = u.id
					AND i.first_access_ts IS NOT NULL
					AND i.starbar_id = $starbarId
				WHERE $user_filter_all
			",
			"new" => "
				SELECT @new_installs := count(distinct(u.id)) AS col1
				FROM user u
				INNER JOIN user_install i
					ON i.user_id = u.id
					AND i.first_access_ts IS NOT NULL
					AND i.starbar_id = $starbarId
				WHERE $user_filter_new
			",
			"active" => "
				SELECT @active_installs := count(distinct(u.id)) AS col1
				FROM user u
				INNER JOIN user_install i
					ON i.user_id = u.id
					AND i.first_access_ts IS NOT NULL
					AND i.starbar_id = $starbarId
				WHERE $user_filter_active
			",
			"inactive" => "SELECT (@total_installs - @active_installs) AS col1",
		];

		$reportsToRun[] = [
			"title" => "Emails without install",
			"total" => "SELECT (@total_emails - @total_installs) AS col1",
			"new" => "SELECT (@new_emails - @new_installs) AS col1",
		];

		$reportsToRun[] = [
			"title" => "Users above level 3",
			"total" => "
				SELECT @total_users_above_3 := count(distinct(u.id)) AS col1
				FROM user u
				WHERE $user_filter_above_3
			",
			"new" => "
				SELECT count(u.id) AS col1
				FROM user u
				WHERE $user_filter_new AND $user_filter_above_3
			",
			"active" => "
				SELECT @active_users_above_3 := count(distinct(u.id)) AS col1
				FROM user u
				WHERE $user_filter_active AND $user_filter_above_3
			",
			"inactive" => "SELECT @total_users_above_3 - @active_users_above_3 AS col1",
		];

		$reportsToRun[] = [
			"title" => "Users Per Level",
			"columns" => ["Level", "# of Users"],
			"total" => "
				SELECT l.ordinal AS col1, count(distinct(b.user_id)) AS col2
				FROM game_level l
				LEFT JOIN game_balance b
					ON l.ordinal = (b.credits-b.debits)
					AND b.game_asset_id = @level_asset
					AND FIND_IN_SET(b.user_id, @users_all)
				WHERE l.game_asset_id = @level_asset
				GROUP BY l.ordinal
				ORDER BY l.ordinal ASC
			",
			"new" => "
				SELECT l.ordinal AS col1, count(distinct(b.user_id)) AS col2
				FROM game_level l
				LEFT JOIN game_balance b
					ON l.ordinal = (b.credits-b.debits)
					AND b.game_asset_id = @level_asset
					AND FIND_IN_SET(b.user_id, @users_new)
				WHERE l.game_asset_id = @level_asset
				GROUP BY l.ordinal
				ORDER BY l.ordinal ASC
			",
			"active" => "
				SELECT l.ordinal AS col1, count(distinct(b.user_id)) AS col2
				FROM game_level l
				LEFT JOIN game_balance b
					ON l.ordinal = (b.credits-b.debits)
					AND b.game_asset_id = @level_asset
					AND FIND_IN_SET(b.user_id, @users_active)
				WHERE l.game_asset_id = @level_asset
				GROUP BY l.ordinal
				ORDER BY l.ordinal ASC
			",
			"inactive" => "
				SELECT l.ordinal AS col1, count(distinct(b.user_id)) AS col2
				FROM game_level l
				LEFT JOIN game_balance b
					ON l.ordinal = (b.credits-b.debits)
					AND b.game_asset_id = @level_asset
					AND FIND_IN_SET(b.user_id, @users_inactive)
				WHERE l.game_asset_id = @level_asset
				GROUP BY l.ordinal
				ORDER BY l.ordinal ASC
			",
		];

		$reportsToRun[] = [
			"section_title" => "Market Research Summary",
			"section_description" => "<b>RA</b> refers to any research actions taken by a user, i.e. a poll, survey, trailer or mission.",
		];

		$reportsToRun[] = [
			"title" => "Number of users who have completed RA in the past week",
			"single" => "
				SELECT count(distinct(sr.user_id)) AS col1
				FROM survey_response sr
				INNER JOIN survey s
					ON s.id = sr.survey_id
					AND s.starbar_id = $starbarId
				WHERE sr.status = 'completed'
					AND sr.completed_disqualified > @one_week_ago
					AND FIND_IN_SET(sr.user_id, @users_all)
			",
		];

		$reportsToRun[] = [
			"title" => "Number of users who have completed RA in the past 3 weeks",
			"single" => "
				SELECT count(distinct(sr.user_id)) AS col1
				FROM survey_response sr
				INNER JOIN survey s
					ON s.id = sr.survey_id
					AND s.starbar_id = $starbarId
				WHERE sr.status = 'completed'
					AND sr.completed_disqualified > @three_weeks_ago
					AND FIND_IN_SET(sr.user_id, @users_all)
			",
		];

		$reportsToRun[] = [
			"title" => "Number of users who have completed RA in the past 2 months",
			"single" => "
				SELECT count(distinct(sr.user_id)) AS col1
				FROM survey_response sr
				INNER JOIN survey s
					ON s.id = sr.survey_id
					AND s.starbar_id = $starbarId
				WHERE sr.status = 'completed'
					AND sr.completed_disqualified > @two_months_ago
					AND FIND_IN_SET(sr.user_id, @users_all)
			",
		];

		$reportsToRun[] = [
			"separator" => true,
		];


		foreach (['RA', 'survey', 'poll', 'trailer', 'mission'] as $type) {
			$reportsToRun[] = [
				"title" => "Total $type completions",
				"single" => "
					SELECT @".$type."_completions := count(sr.id) AS col1
					FROM survey_response sr
					INNER JOIN survey s
						ON s.id = sr.survey_id
						AND s.starbar_id = $starbarId
						" . ($type != 'RA' ? "AND s.type = '$type'" : "") . "
					WHERE sr.status = 'completed'
						AND FIND_IN_SET(sr.user_id, @users_all)
				",
			];

			$reportsToRun[] = [
				"title" => "Average $type completions per installed user",
				"single" => " SELECT ROUND(@".$type."_completions / @total_installs, 1) AS col1 ",
			];

			$reportsToRun[] = [
				"title" => "Total $type completions in the last week",
				"single" => "
					SELECT count(sr.id) AS col1
					FROM survey_response sr
					INNER JOIN survey s
						ON s.id = sr.survey_id
						AND s.starbar_id = $starbarId
						" . ($type != 'RA' ? "AND s.type = '$type'" : "") . "
					WHERE sr.status = 'completed'
						AND sr.completed_disqualified > @one_week_ago
						AND FIND_IN_SET(sr.user_id, @users_all)
				",
			];

			$reportsToRun[] = [
				"separator" => true,
			];

		}

		$reportsToRun[] = [
			"section_title" => "Economy Summary",
			"section_description" => "",
		];

		$reportsToRun[] = [
			"title" => "Total unspent coins in economy",
			"single" => "
				SELECT @total_unspent_red := IFNULL(SUM(credits-debits), 0) AS col1
				FROM game_balance
				WHERE game_asset_id = @red_asset
					AND FIND_IN_SET(user_id, @users_all)
			",
		];

		$reportsToRun[] = [
			"title" => "Average unspent coins per installed user",
			"single" => " SELECT ROUND(@total_unspent_red / @total_installs, 1) AS col1 ",
		];

		$reportsToRun[] = [
			"separator" => true,
		];

		$reportsToRun[] = [
			"title" => "Total unspent coins for active users",
			"single" => "
				SELECT @active_unspent_red := IFNULL(SUM(credits-debits), 0) AS col1
				FROM game_balance
				WHERE game_asset_id = @red_asset
					AND FIND_IN_SET(user_id, @users_active)
			",
		];

		$reportsToRun[] = [
			"title" => "Average unspent coins per active user",
			"single" => " SELECT ROUND(@active_unspent_red / @active_installs, 1) AS col1 ",
		];

		$reportsToRun[] = [
			"separator" => true,
		];

		$reportsToRun[] = [
			"title" => "Total coins spent during the last 3 weeks",
			"single" => "
				SELECT -IFNULL(SUM(l.amount), 0) AS col1
				FROM game_transaction_line l
				INNER JOIN game_transaction t
					ON t.id = l.game_transaction_id
					AND t.ts > @three_weeks_ago
					AND FIND_IN_SET(t.user_id, @users_all)
				WHERE l.amount < 0
					AND l.game_asset_id = @red_asset
			",
		];

		$reportsToRun[] = [
			"title" => "Total coins earned during the last 3 weeks",
			"single" => "
				SELECT SUM(l.amount) AS col1
				FROM game_transaction_line l
				INNER JOIN game_transaction t
					ON t.id = l.game_transaction_id
					AND t.ts > @three_weeks_ago
					AND FIND_IN_SET(t.user_id, @users_all)
				WHERE l.amount > 0
					AND l.game_asset_id = @red_asset
			",
		];

		$reportsToRun[] = [
			"title" => "Total coins earned in the last week",
			"single" => "
				SELECT SUM(l.amount) AS col1
				FROM game_transaction_line l
				INNER JOIN game_transaction t
					ON t.id = l.game_transaction_id
					AND t.ts > @one_week_ago
					AND FIND_IN_SET(t.user_id, @users_all)
				WHERE l.amount > 0
					AND l.game_asset_id = @red_asset
			",
		];

		$reportsToRun[] = [
			"section_title" => "Reward Summary",
			"section_description" => "",
		];

		$reportsToRun[] = [
			"title" => "Number of users who have redeemed any items in the past 3 weeks",
			"single" => "
				SELECT count(u.id) AS col1
				FROM user u
				INNER JOIN user_gaming_order_history h
					ON h.user_id = u.id
					AND h.created > @three_weeks_ago
				WHERE $user_filter_all
			",
		];

		$reportsToRun[] = [
			"title" => "Number of users who have redeemed any items (lifetime)",
			"single" => "
				SELECT @total_redeemers := count(distinct(u.id)) AS col1
				FROM user u
				INNER JOIN user_gaming_order_history h
					ON h.user_id = u.id
				WHERE $user_filter_all
			",
		];

		$reportsToRun[] = [
			"title" => "Percentage of installers who have redeemed an item (lifetime)",
			"single" => " SELECT CONCAT(ROUND(@total_redeemers * 100 / @total_installs, 1), '%') AS col1 ",
		];

		$reportsToRun[] = [
			"separator" => true,
		];

		foreach (['physical', 'virtual', 'token'] as $type) {
			$reportsToRun[] = [
				"title" => "Number of users who have redeemed $type items in the past 3 weeks",
				"single" => "
					SELECT count(distinct(u.id)) AS col1
					FROM user u
					INNER JOIN user_gaming_order_history h
						ON h.user_id = u.id
						AND h.created > @three_weeks_ago
					INNER JOIN game_purchasable p
						ON p.game_asset_id = h.game_asset_id
						AND p.type = '$type'
					WHERE $user_filter_all
				",
			];

			$reportsToRun[] = [
				"title" => "Number of $type items redeemed in the past 3 weeks",
				"single" => "
					SELECT @".$type."_redeemed_recent := count(h.id) AS col1
					FROM user_gaming_order_history h
					INNER JOIN game_purchasable p
						ON p.game_asset_id = h.game_asset_id
						AND p.type = '$type'
					WHERE h.created > @three_weeks_ago
						AND FIND_IN_SET(h.user_id, @users_all)
				",
			];

			$reportsToRun[] = [
				"title" => "Average number of $type items redeemed per active user in the past 3 weeks",
				"single" => " SELECT ROUND(@".$type."_redeemed_recent / IF(@active_installs>0,@active_installs,1), 1) AS col1 ",
			];

			$reportsToRun[] = [
				"separator" => true,
			];
		}

		$reportsToRun[] = [
			"section_title" => "Social Summary",
			"section_description" => "",
		];

		foreach(['facebook', 'twitter'] as $network) {
			$reportsToRun[] = [
				"title" => "Number of users who have connected their $network account",
				"single" => " SELECT count(id) AS col1 FROM user_social WHERE provider = '$network' AND FIND_IN_SET(user_id, @users_all) ",
			];

			foreach (['RA', 'survey', 'poll', 'trailer'] as $type) {
				$toSearch = ($network == 'facebook' ? "FB" : "TW") . ($type != 'RA' ? "_" . strtoupper($type) : "") . "_%_SHARE";

				if ($type == 'survey') {
					$reportsToRun[] = [
						"title" => "",
						"value" => "",
					];
				}

				$reportsToRun[] = [
					"title" => "Number of users who have shared at least one $type on $network",
					"single" => "
						SELECT count(distinct(user_id)) AS col1 FROM
						(
							SELECT distinct(ug.user_id)
							FROM user_gaming ug
							INNER JOIN user_gaming_transaction_history ugth
								ON ug.id = ugth.user_gaming_id
								AND ugth.action LIKE '$toSearch'
							WHERE ug.starbar_id = $starbarId
								AND FIND_IN_SET(ug.user_id, @users_all)
						UNION
							SELECT distinct(t.user_id)
							FROM game_transaction t
							INNER JOIN game_transaction_type tt
								ON t.game_transaction_type_id = tt.id
								AND tt.short_name LIKE '$toSearch'
							INNER JOIN survey s
								ON s.id = t.survey_id
								AND s.starbar_id = $starbarId
								" . ($type != 'RA' ? "AND s.type = '$type'" : "") . "
							WHERE FIND_IN_SET(t.user_id, @users_all)
						) AS upupdowndownleftrightleftrightbastart
					",
				];

			}

			$reportsToRun[] = [
				"separator" => true,
			];
		}

		/*
		 * Add reports here! Format:

		// single query -- note that you need to select the column you want as col1
		$reportsToRun[] = [
			"title" => "Title of this value",
			"single" => " SELECT xyz AS col1 FROM ... "
		];

		// single value
		$reportsToRun[] = [
			"title" => "Title of this value",
			"value" => " 123 "
		];

		// section start
		$reportsToRun[] = [
			"section_title" => "Market Research Summary",
			"section_description" => "<b>RA</b> refers to any research actions taken by a user, i.e. a poll, survey, trailer or mission.",
		];

		// separator
		$reportsToRun[] = [
			"separator" => true,
		];
		 *
		 *
		 */



		// process $reportsToRun
		foreach ($reportsToRun as $i => $run) {
			$reportResults[$i] = [];

			if (isset($run['section_title']) || isset($run['separator'])) {
				$reportResults[$i] = $run;
			} else {
				$reportResults[$i]['title'] = $run['title'];

				if (isset($run['value'])) {
					$reportResults[$i]['single'] = $run['value'];
				} else {
					foreach(['single', 'total', 'new', 'active', 'inactive'] as $userFilter) {
						if (isset($run[$userFilter])) {
							if (isset($run['columns'])) {
								$reportResults[$i]['columns'] = $run['columns'];
								$reportResults[$i][$userFilter.'_table'] = [];
								$tempRows = Db_Pdo::fetchAll($run[$userFilter]);
								foreach ($tempRows as $row) {
									$reportResults[$i][$userFilter.'_table'][$row['col1']] = $row['col2'];
								}
							} else {
								$reportResults[$i][$userFilter] = Db_Pdo::fetch($run[$userFilter])['col1'];
							}
						}
					}
				}
			}
		}

		return $reportResults;
	}
}
