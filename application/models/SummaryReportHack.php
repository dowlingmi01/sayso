<?
class SummaryReportHack {
	static public function getReportResults($starbarId) {
		if (!$starbarId) return;

		$reportResults = array();
		$reportsToRun = array();

		// set up session variables
		$sql = "
			SET @three_weeks_ago = (CURRENT_DATE - INTERVAL 3 WEEK);
			SELECT @users_new := group_concat(id) FROM user WHERE created > @three_weeks_ago;
			SELECT @mpv_id_three_weeks_ago := id FROM metrics_page_view WHERE created < @three_weeks_ago ORDER BY id DESC LIMIT 1;
			SELECT @users_active := group_concat(user_id) FROM (SELECT DISTINCT user_id FROM metrics_page_view WHERE id > @mpv_id_three_weeks_ago) AS blahblah;
			SELECT @users_inactive := group_concat(id) FROM user WHERE NOT FIND_IN_SET(id, @users_active);
			SELECT @level_asset := a.id FROM game_asset a INNER JOIN economy e ON e.id = a.economy_id INNER JOIN starbar s ON s.economy_id = e.id AND s.id = $starbarId WHERE a.type = 'level';
			SELECT @users_above_3 := group_concat(u.id) FROM user u INNER JOIN game_balance b ON u.id = b.user_id AND (b.credits - b.debits) >= 3 AND b.game_asset_id = @level_asset;
		";

		Db_Pdo::execute($sql);

		$user_filter = " WHERE u.type != 'test' ";
		$user_filter_new = " AND FIND_IN_SET(u.id, @users_new) ";
		$user_filter_active = " AND FIND_IN_SET(u.id, @users_active) ";
		$user_filter_inactive = " AND FIND_IN_SET(u.id, @users_inactive) ";
		$user_filter_above_3 = " AND FIND_IN_SET(u.id, @users_above_3) ";


		// set up all the reports
		$reportsToRun[] = [
			"section_title" => "User Summary",
			"section_description" => "In the table below, <b>New Users</b> means users who have registered less than 3 weeks ago.<br /><b>Active Users</b> refers to users who have had the browser app installed in the past 3 weeks (i.e. behavioral data collected in past 3 weeks).",
		];

		$reportsToRun[] = [
			"title" => "Email addresses collected",
			"total" => "
				SELECT @total_emails := count(u.id) AS col1
				FROM user u
				INNER JOIN user_install i
					ON i.user_id = u.id
					AND i.starbar_id = $starbarId
				$user_filter
			",
			"new" => "
				SELECT @new_emails := count(u.id) AS col1
				FROM user u
				INNER JOIN user_install i
					ON i.user_id = u.id
					AND i.starbar_id = $starbarId
				$user_filter $user_filter_new
			",
		];

		$reportsToRun[] = [
			"title" => "Installs",
			"total" => "
				SELECT @total_installs := count(u.id) AS col1
				FROM user u
				INNER JOIN user_install i
					ON i.user_id = u.id
					AND i.first_access_ts IS NOT NULL
					AND i.starbar_id = $starbarId
				$user_filter
			",
			"new" => "
				SELECT @new_installs := count(u.id) AS col1
				FROM user u
				INNER JOIN user_install i
					ON i.user_id = u.id
					AND i.first_access_ts IS NOT NULL
					AND i.starbar_id = $starbarId
				$user_filter $user_filter_new
			",
			"active" => "
				SELECT @active_installs := count(u.id) AS col1
				FROM user u
				INNER JOIN user_install i
					ON i.user_id = u.id
					AND i.first_access_ts IS NOT NULL
					AND i.starbar_id = $starbarId
				$user_filter $user_filter_active
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
				SELECT @total_users_above_3 := count(u.id) AS col1
				FROM user u
				$user_filter $user_filter_above_3
			",
			"new" => "
				SELECT count(u.id) AS col1
				FROM user u
				$user_filter $user_filter_new $user_filter_above_3
			",
			"active" => "
				SELECT @active_users_above_3 := count(u.id) AS col1
				FROM user u
				$user_filter $user_filter_active $user_filter_above_3
			",
			"inactive" => "SELECT @total_users_above_3 - @active_users_above_3 AS col1",
		];

		$reportsToRun[] = [
			"title" => "Users Per Level",
			"columns" => ["Level", "# of Users"],
			"total" => "
				SELECT l.ordinal AS col1, count(b.user_id) AS col2
				FROM game_level l
				LEFT JOIN game_balance b
					ON l.ordinal = (b.credits-b.debits)
					AND b.game_asset_id = @level_asset
				WHERE l.game_asset_id = @level_asset
				GROUP BY l.ordinal
				ORDER BY l.ordinal ASC
			",
			"new" => "
				SELECT l.ordinal AS col1, count(b.user_id) AS col2
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
				SELECT l.ordinal AS col1, count(b.user_id) AS col2
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
				SELECT l.ordinal AS col1, count(b.user_id) AS col2
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
					AND sr.completed_disqualified > (now() - interval 1 week)
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
					AND sr.completed_disqualified > (now() - interval 3 week)
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
					AND sr.completed_disqualified > (now() - interval 2 month)
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
				",
			];

			$reportsToRun[] = [
				"title" => "Average $type completions per installed user",
				"single" => " SELECT ROUND(@".$type."_completions / @total_installs, 1) AS col1 ",
			];

			$reportsToRun[] = [
				"title" => "Total $type completions in the last week",
				"single" => "
					SELECT @".$type."_completions := count(sr.id) AS col1
					FROM survey_response sr
					INNER JOIN survey s
						ON s.id = sr.survey_id
						AND s.starbar_id = $starbarId
						" . ($type != 'RA' ? "AND s.type = '$type'" : "") . "
					WHERE sr.status = 'completed'
						AND sr.completed_disqualified > (now() - interval 1 week)
				",
			];

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
