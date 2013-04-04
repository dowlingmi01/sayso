CREATE or REPLACE
VIEW `view_user_install_abandon` AS
SELECT DISTINCT
	`i`.`location` AS `location`,
	`v`.`location` AS `installlocation`,
	`i`.`id` AS `id`,
	`i`.`external_user_id` AS `external_user_id`,
	`e`.`email` AS `email`,
	`s`.`label` AS `starbar`,
	`i`.`ip_address` AS `ip_address`,
	`i`.`user_agent` AS `user_agent`,
	`operatingsystem`(`i`.`user_agent`)AS `operatingsystem`,
	`browser`(`i`.`user_agent`)AS `browser`,
	`i`.`user_agent_supported` AS `user_agent_supported`,
	`i`.`origination` AS `origination`,
	`i`.`url` AS `url`,
	`i`.`referrer` AS `referrer`,
	`i`.`client_data` AS `client_data`,
	`i`.`visited_ts` AS `visited_ts`,
	`i`.`click_ts` AS `click_ts`,
	`i`.`first_access_ts` AS `first_access_ts`,
	`i`.`created` AS `created`,
	`i`.`modified` AS `modified`
FROM
	(
		(
			(
				`user_install` `i`
				LEFT JOIN `starbar` `s` ON(
					(`s`.`id` = `i`.`starbar_id`)
				)
			)
			LEFT JOIN `user_email` `e` ON(
				(
					`i`.`user_id` = `e`.`user_id`
				)
			)
		)
		LEFT JOIN `view_user_install` `v` ON(
			(
				`v`.`location` = `i`.`location`
			)
		)
	)
WHERE
	(
		isnull(`i`.`first_access_ts`)
		AND(
			NOT(
				(`e`.`email` LIKE '%@say.so')
			)
		)
		AND isnull(`v`.`location`)
	)
GROUP BY
	`i`.`location` ;

